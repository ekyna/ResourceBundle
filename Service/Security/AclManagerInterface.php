<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Config\NamespaceConfig;
use Ekyna\Component\Resource\Config\Registry\NamespaceRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AclManagerInterface
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AclManagerInterface
{
    /**
     * Returns the namespace registry.
     *
     * @return NamespaceRegistryInterface
     */
    public function getNamespaceRegistry(): NamespaceRegistryInterface;

    /**
     * Returns the resource registry.
     *
     * @return ResourceRegistryInterface
     */
    public function getResourceRegistry(): ResourceRegistryInterface;

    /**
     * Sets the namespace permissions value.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param bool                $value
     *
     * @return bool
     */
    public function setNamespace(AclSubjectInterface $subject, string $namespace, bool $value): bool;

    /**
     * Sets the resource permissions value.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param bool                $value
     *
     * @return bool
     */
    public function setResource(AclSubjectInterface $subject, string $namespace, string $resource, bool $value): bool;

    /**
     * Sets the permission value.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $name
     * @param bool                $value
     *
     * @return bool Whether the ace has been changed
     */
    public function setPermission(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name,
        bool $value
    ): bool;

    /**
     * Synchronizes the database with changes.
     */
    public function flush(): void;

    /**
     * Returns the subject access control list.
     *
     * @param AclSubjectInterface $subject
     *
     * @return array
     */
    public function getAcl(AclSubjectInterface $subject): array;

    /**
     * Returns the namespace access control list.
     *
     * @param AclSubjectInterface $subject
     * @param NamespaceConfig     $config
     *
     * @return array|null
     */
    public function getNamespace(AclSubjectInterface $subject, NamespaceConfig $config): ?array;

    /**
     * Returns the resource access control list.
     *
     * @param AclSubjectInterface $subject
     * @param ResourceConfig      $config
     *
     * @return array|null
     */
    public function getResource(AclSubjectInterface $subject, ResourceConfig $config): ?array;

    /**
     * Returns the access control entry.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $name
     *
     * @return array
     */
    public function getPermission(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name
    ): array;

    /**
     * Returns whether the given subject has access granted for the given resource and permission.
     *
     * @param AclSubjectInterface      $subject
     * @param ResourceInterface|string $resource
     * @param string                   $permission
     *
     * @return bool
     */
    public function isGranted(AclSubjectInterface $subject, $resource, string $permission): bool;
}
