<?php

namespace App\Controller\Admin;

use App\Entity\Header;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HeaderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Header::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title','Titre du Header'),
            TextareaField::new('content','Contenu de notre Header'),
            TextField::new('btnTitle','Titre de notre bouton'),
            TextField::new('btnUrl','Url de destination de notre bouton'),
            ImageField::new('illustration')
                ->setUploadDir('public/uploads')
                ->setBasePath('uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
        ];
    }
}
