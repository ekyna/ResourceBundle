<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DataFixtures;

use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Class TranslationFactory
 * @package Ekyna\Bundle\ResourceBundle\DataFixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TranslationFactory
{
    public static function create(TranslatableInterface $translatable): TranslationInterface
    {
        return $translatable->translate(null, true);
    }
}
