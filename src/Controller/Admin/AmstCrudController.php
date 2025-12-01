<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Survos\EzBundle\Controller\BaseCrudController;

class AmstCrudController extends BaseCrudController
{
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('object', 'object.label');
        yield TextField::new('vondstnummer', 'vondstnummer.label');
        yield TextField::new('subcategorie', label: 'subcategorie.label');
//        yield TextField::new('niveau1');
//        yield TextField::new('niveau2');
//        yield TextField::new('niveau3');
//        yield TextField::new('niveau4');
        foreach (parent::configureFields($pageName) as $field) {
            $dto = $field->getAsDto();
            // avoid dups!
            if (in_array($dto->getProperty(), ['object','vondstnummer','subcategorie'])) {
                continue;
            }
            $field->setLabel($dto->getProperty() . '.label');
            $field->setHelp($dto->getProperty() . '.description');
            yield $field;
        }

    }
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Amst::class;
    }


}
