<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Behavior;

use Ekyna\Component\Resource\Behavior\AbstractBehavior;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Model\IsDefaultInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class IsDefaultBehavior
 * @package Ekyna\Component\Resource\Behavior\IsDefault
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class IsDefaultBehavior extends AbstractBehavior
{
    /**
     * @inheritDoc
     */
    public function onInsert(ResourceInterface $resource, array $options): void
    {
        // TODO: Implement onInsert() method.
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceInterface $resource, array $options): void
    {
        // TODO: Implement onUpdate() method.
    }

    /**
     * @inheritDoc
     */
    public function onDelete(ResourceInterface $resource, array $options): void
    {
        // TODO: Implement onDelete() method.
    }

    /**
     * @inheritDoc
     */
    public static function configureBehavior(): array
    {
        return [
            'name'       => 'is_default',
            'interface'  => IsDefaultInterface::class,
            'operations' => [
                Behaviors::INSERT,
            ],
            'options'    => [
                'property'       => 'default',
                'group_property' => null,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['property', 'group_property'])
            ->setAllowedTypes('property', 'string')
            ->setAllowedTypes('group_property', 'string');
    }

    /**
     * @inheritDoc
     */
    public static function buildActions(array $actions, array $resource, array $options): array
    {
        return [];
    }
}
