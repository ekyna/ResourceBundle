<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Behavior;

use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploaderInterface;
use Ekyna\Component\Resource\Behavior\AbstractBehavior;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Model\UploadableInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_subclass_of;
use function sprintf;

/**
 * Class UploadableBehavior
 * @package Ekyna\Bundle\ResourceBundle\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploadableBehavior extends AbstractBehavior
{
    public static function configureBehavior(): array
    {
        return [
            'name'       => 'uploadable',
            'interface'  => UploadableInterface::class,
            'operations' => [
                Behaviors::METADATA,
            ],
            'options'    => [],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        /*
         * TODO:
         * - replace 'uploader' option with 'filesystem' option.
         * - automatically configure filesystem (one per resource)
         * - register filesystem (with registry: resource <-> filesystem map)
         * - automatically configure uploader
         * - automatically configure download action
         * - automatically configure upload action (?)
         */

        $resolver
            ->setRequired('uploader')
            ->setAllowedTypes('uploader', 'string');
    }

    public static function buildContainer(ContainerBuilder $container, ResourceConfig $resource, array $options): void
    {
        // Check configured uploader
        $uploaderId = $options['uploader'];
        $definition = $container->getDefinition($uploaderId);
        if (!is_subclass_of($class = $definition->getClass(), UploaderInterface::class)) {
            throw new LogicException(sprintf(
                "Service '%s' (%s) must implements %s",
                $uploaderId, $class, UploaderInterface::class
            ));
        }

        // Register resource -> uploader mapping
        $container
            ->getDefinition('ekyna_resource.uploader_resolver')
            ->addMethodCall('register', [$resource->getEntityClass(), $uploaderId]);

        // Add tags to uploadable listener
        $definition = $container->getDefinition('ekyna_resource.listener.uploadable');

        // TODO Depends on driver
        /** @see \Ekyna\Component\Resource\Doctrine\ORM\OrmExtension::DRIVER */

        $events = [
            'prePersist',
            'postPersist',
            'preUpdate',
            'postUpdate',
            'preRemove',
            'postRemove',
        ];

        foreach ($events as $event) {
            $definition->addTag('doctrine.orm.entity_listener', [
                'entity' => $resource->getEntityClass(),
                'event'  => $event,
            ]);
        }
    }
}
