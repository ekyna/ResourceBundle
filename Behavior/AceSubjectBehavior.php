<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Behavior;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclIdGenerator;
use Ekyna\Component\Resource\Behavior\AbstractBehavior;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceAwareInterface;
use Ekyna\Component\Resource\Persistence\PersistenceAwareTrait;

/**
 * Class AceSubjectBehavior
 * @package Ekyna\Bundle\ResourceBundle\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AceSubjectBehavior extends AbstractBehavior implements PersistenceAwareInterface
{
    use PersistenceAwareTrait;

    private AclIdGenerator $generator;

    public function __construct(AclIdGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritDoc
     *
     * @param AclSubjectInterface $resource
     */
    public function onInsert(ResourceInterface $resource, array $options): void
    {
        if (!$resource instanceof AclSubjectInterface) {
            throw new UnexpectedTypeException($resource, AclSubjectInterface::class);
        }

        if (!$this->generator->generate($resource)) {
            return;
        }

        $this->getPersistenceHelper()->persistAndRecompute($resource, false);
    }

    /**
     * @inheritDoc
     */
    public function onMetadata(ClassMetadataInfo $metadata, array $options): void
    {
        if (!$metadata->getReflectionClass()->implementsInterface(AclSubjectInterface::class)) {
            return;
        }

        if (!$metadata->hasField('aclSubjectId')) {
            $metadata->mapField([
                'fieldName'  => 'aclSubjectId',
                'columnName' => 'acl_subject_id',
                'type'       => 'string',
                'length'     => 23,
                'nullable'   => false,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function configureBehavior(): array
    {
        return [
            'name'       => 'ace_subject',
            'interface'  => AclSubjectInterface::class,
            'operations' => [
                Behaviors::METADATA,
                Behaviors::INSERT,
            ],
            'options'    => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function buildActions(array $actions, array $resource, array $options): array
    {
        $built = [];

        if (class_exists('Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\PermissionAction')) {
            if (!isset($actions['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\PermissionAction'])) {
                $built['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\PermissionAction'] = null;
            }
        }

        if (class_exists('Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\ResourceAction')) {
            if (!isset($actions['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\ResourceAction'])) {
                $built['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\ResourceAction'] = null;
            }
        }

        if (class_exists('Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\NamespaceAction')) {
            if (!isset($actions['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\NamespaceAction'])) {
                $built['Ekyna\\Bundle\\AdminBundle\\Action\\Acl\\NamespaceAction'] = null;
            }
        }

        return $built;
    }
}
