<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Survos\BabelBundle\Entity\Base\StrTranslationBase;
use App\Repository\StrTranslationRepository;
// add imports if not present:

#[ORM\Table(indexes: [
    new ORM\Index(name: 'idx_strtr_hash', columns: ['hash']),
    new ORM\Index(name: 'idx_strtr_locale', columns: ['locale']),
    new ORM\Index(name: 'idx_strtr_hash_locale', columns: ['hash', 'locale']),
])]


#[ORM\Entity(repositoryClass: StrTranslationRepository::class)]
class StrTranslation extends StrTranslationBase {}
