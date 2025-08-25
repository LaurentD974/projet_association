<?php
// src/Form/CsvUploadType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class CsvUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('csv_file', FileType::class, [
            'label' => 'Importer un fichier CSV',
            'mapped' => false,
            'required' => true,
        ]);
    }
}