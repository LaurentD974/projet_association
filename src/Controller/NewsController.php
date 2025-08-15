<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/news')]
final class NewsController extends AbstractController
{
    #[Route('/', name: 'admin_news_index', methods: ['GET'])]
    public function index(NewsRepository $newsRepository): Response
    {
        $newsList = $newsRepository->findAll();

        return $this->render('admin/news/index.html.twig', [
            'newsList' => $newsList,
        ]);
    }

    #[Route('/new', name: 'admin_news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $news->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($news);
            $entityManager->flush();

            $this->addFlash('success', 'Actualité créée avec succès.');

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->render('admin/news/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_news_show', methods: ['GET'])]
    public function show(News $news): Response
    {
        if (!$news->isPublished() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Cette actualité est privée.');
        }

        return $this->render('admin/news/show.html.twig', [
            'news' => $news,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Actualité modifiée avec succès.');

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->render('admin/news/edit.html.twig', [
            'form' => $form,
            'news' => $news,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($news);
            $entityManager->flush();

            $this->addFlash('success', 'Actualité supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_news_index');
    }
}