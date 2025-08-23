<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\AdminEventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CsvUploadType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Entreprise;

#[Route('/admin')]
class AdminController extends AbstractController
{
  #[Route('/dashboard', name: 'admin_dashboard')]
public function dashboard(EventRepository $eventRepo, UserRepository $userRepo): Response
{
    $pendingCount = count($eventRepo->findBy(['isValidated' => false]));
    $totalCount = count($eventRepo->findAll());

    $users = $userRepo->findAll();
    $totalUsers = count($users);
    $totalCompagnons = count(array_filter($users, fn($u) => $u->getStatut() === 'Compagnon'));

    $metierCounts = [];
    foreach ($users as $user) {
        $metier = $user->getMetier();
        if ($metier) {
            $metierCounts[$metier] = ($metierCounts[$metier] ?? 0) + 1;
        }
    }

    // 🔍 Résumé des événements dans les 2 mois à venir
    $now = new \DateTime();
    $threeMonthsLater = (clone $now)->modify('+2 months');

   $upcomingEvents = array_filter($eventRepo->findBy(['isValidated' => true]), function ($event) use ($now, $threeMonthsLater) {
    return $event->getStartDate() >= $now && $event->getStartDate() <= $threeMonthsLater;
});

// 🗂️ Tri par date croissante
usort($upcomingEvents, fn($a, $b) => $a->getStartDate() <=> $b->getStartDate());

    return $this->render('admin/dashboard.html.twig', [
        'pendingCount' => $pendingCount,
        'totalCount' => $totalCount,
        'totalUsers' => $totalUsers,
        'totalCompagnons' => $totalCompagnons,
        'metierCounts' => $metierCounts,
        'upcomingEvents' => $upcomingEvents, // ✅ ajout ici
    ]);
}

    #[Route('/events', name: 'admin_events_validate')]
    public function validateEvents(EventRepository $repo): Response
    {
        $pending = $repo->findBy(['isValidated' => false]);
        return $this->render('admin/events/pending.html.twig', ['events' => $pending]);
    }

    #[Route('/events/list', name: 'admin_events_list')]
    public function listEvents(EventRepository $repo): Response
    {
        $events = $repo->findAll();
        return $this->render('admin/events/list.html.twig', ['events' => $events]);
    }

    #[Route('/events/new', name: 'admin_events_new')]
    public function newEvent(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(AdminEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('admin_events_list');
        }

        return $this->render('admin/events/form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/events/{id}/edit', name: 'admin_events_edit')]
    public function editEvent(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('admin_events_list');
        }

        return $this->render('admin/events/form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('danger', 'Événement supprimé.');
        }

        return $this->redirectToRoute('admin_events_list');
    }

    #[Route('/events/{id}/validate', name: 'admin_events_validate_one')]
    public function validateOne(Event $event, EntityManagerInterface $em): Response
    {
        $event->setIsValidated(true);
        $em->flush();
        $this->addFlash('success', 'Événement validé.');
        return $this->redirectToRoute('admin_events_list');
    }

    #[Route('/import-csv', name: 'admin_import_csv')]
    public function importCsv(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv_file')->getData();
            $path = $file->getPathname();

            $handle = fopen($path, 'r');
            $headers = fgetcsv($handle, null, ';');

            while (($row = fgetcsv($handle, null, ';')) !== false) {
                $data = array_combine($headers, $row);
                $email = $data['email'] ?? null;

                if (!$email) {
                    continue;
                }

                $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                $isNew = false;
                $changes = [];

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $isNew = true;
                }

                $fields = [
                    'prenom' => ['getter' => 'getPrenom', 'setter' => 'setPrenom'],
                    'nom' => ['getter' => 'getNom', 'setter' => 'setNom'],
                    'metier' => ['getter' => 'getMetier', 'setter' => 'setMetier'],
                    'statut' => ['getter' => 'getStatut', 'setter' => 'setStatut'],
                    'position' => ['getter' => 'getPosition', 'setter' => 'setPosition'],
                    'nom_province' => ['getter' => 'getNomProvince', 'setter' => 'setNomProvince'],
                    'nom_compagnon' => ['getter' => 'getNomCompagnon', 'setter' => 'setNomCompagnon'],
                    'telephone' => ['getter' => 'getTelephone', 'setter' => 'setTelephone'],
                    'fonction1' => ['getter' => 'getFonction1', 'setter' => 'setFonction1'],
                    'fonction2' => ['getter' => 'getFonction2', 'setter' => 'setFonction2'],
                    'adresse1' => ['getter' => 'getAdresse1', 'setter' => 'setAdresse1'],
                    'adresse2' => ['getter' => 'getAdresse2', 'setter' => 'setAdresse2'],
                    'code_postale' => ['getter' => 'getCodePostale', 'setter' => 'setCodePostale'],
                    'ville' => ['getter' => 'getVille', 'setter' => 'setVille'],
                    'droit' => ['getter' => 'getDroit', 'setter' => 'setDroit'],
                    'photo' => ['getter' => 'getPhoto', 'setter' => 'setPhoto'],
                    'passetemps' => ['getter' => 'getPassetemps', 'setter' => 'setPassetemps'],
                ];

                foreach ($fields as $key => $methods) {
                    $newValue = $data[$key] ?? null;
                    $currentValue = $user->{$methods['getter']}();

                    if ($newValue !== null && $newValue != $currentValue) {
                        $user->{$methods['setter']}($newValue);
                        $changes[] = "$key: '$currentValue' → '$newValue'";
                    }
                }

                if ($isNew || !$user->getPassword()) {
                    $plainPassword = $data['password'] ?? 'temp123';
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                    $changes[] = "password: [hashed]";
                }

                // Gestion des rôles
                $rawRoles = trim($data['roles'] ?? ''); // récupère et nettoie le champ

                // Si vide ou invalide, on applique ROLE_USER
                $roles = json_decode($rawRoles, true);
                if (!is_array($roles) || count($roles) === 0) {
                    $roles = ['ROLE_USER'];
                }

                $user->setRoles($roles);
                $changes[] = "roles: " . json_encode($roles);

                if (!empty($data['entreprise_id'])) {
                    $entreprise = $em->getRepository(Entreprise::class)->find($data['entreprise_id']);
                    if ($entreprise && $entreprise !== $user->getEntreprise()) {
                        $user->setEntreprise($entreprise);
                        $changes[] = "entreprise_id: " . $data['entreprise_id'];
                    }
                }

                if (!empty($data['date_arrivee'])) {
                    $dateArrivee = new \DateTime($data['date_arrivee']);
                    if ($user->getDateArrivee() != $dateArrivee) {
                        $user->setDateArrivee($dateArrivee);
                        $changes[] = "date_arrivee: " . $data['date_arrivee'];
                    }
                }

                if (!empty($data['date_depart'])) {
                    $dateDepart = new \DateTime($data['date_depart']);
                    if ($user->getDateDepart() != $dateDepart) {
                        $user->setDateDepart($dateDepart);
                        $changes[] = "date_depart: " . $data['date_depart'];
                    }
                }

                $em->persist($user);
                $em->flush(); // ← flush à chaque tour pour garantir la persistance

                if ($isNew) {
                    $this->addFlash('success', "✅ Nouvel utilisateur créé : $email");
                } elseif (!empty($changes)) {
                    $this->addFlash('info', "🔄 Utilisateur mis à jour : $email → " . implode(', ', $changes));
                }
            }

            fclose($handle);

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/import_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}