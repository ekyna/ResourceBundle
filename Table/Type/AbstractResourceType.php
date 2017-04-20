<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractResourceType
 * @package Ekyna\Bundle\ResourceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractResourceType extends AbstractTableType
{
    protected string $dataClass;

    public function setDataClass(string $class): void
    {
        $this->dataClass = $class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'source' => new EntitySource($this->dataClass),
        ]);
    }
}
