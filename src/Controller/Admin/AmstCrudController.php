<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Survos\EzBundle\Controller\BaseCrudController;

class AmstCrudController extends BaseCrudController
{
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('object');
        yield TextField::new('subcategorie');
        yield TextField::new('niveau1');
        yield TextField::new('niveau2');
        yield TextField::new('niveau3');
        yield TextField::new('niveau4');
        foreach (parent::configureFields($pageName) as $field) {
            yield $field;
        }

    }
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Amst::class;
    }
}
