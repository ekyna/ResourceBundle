<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\ResourceBundle\Entity\AccessControlEntry;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Bundle\ResourceBundle\Repository\AceRepository;
use Ekyna\Component\Resource\Config\NamespaceConfig;
use Ekyna\Component\Resource\Config\Registry\NamespaceRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Psr\Cache\CacheItemPoolInterface;

use function array_replace;

/**
 * Class AclManager
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AclManager implements AclManagerInterface
{
    private PermissionRegistryInterface $permissionRegistry;
    private NamespaceRegistryInterface  $namespaceRegistry;
    private ResourceRegistryInterface   $resourceRegistry;
    private AceRepository               $repository;
    private CacheItemPoolInterface      $itemCache;
    private array                       $aclCache;
    private EntityManagerInterface      $wrapped;


    /**
     * Constructor.
     *
     * @param PermissionRegistryInterface $permissionRegistry
     * @param NamespaceRegistryInterface  $namespaceRegistry
     * @param ResourceRegistryInterface   $resourceRegistry
     * @param AceRepository               $repository
     * @param ManagerRegistry             $registry
     * @param CacheItemPoolInterface      $itemCache
     */
    public function __construct(
        PermissionRegistryInterface $permissionRegistry,
        NamespaceRegistryInterface $namespaceRegistry,
        ResourceRegistryInterface $resourceRegistry,
        AceRepository $repository,
        ManagerRegistry $registry,
        CacheItemPoolInterface $itemCache
    ) {
        $this->permissionRegistry = $permissionRegistry;
        $this->namespaceRegistry = $namespaceRegistry;
        $this->resourceRegistry = $resourceRegistry;
        $this->repository = $repository;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->wrapped = $registry->getManagerForClass(AccessControlEntry::class);
        $this->itemCache = $itemCache;
        $this->aclCache = [];
    }

    /**
     * @inheritDoc
     */
    public function getNamespaceRegistry(): NamespaceRegistryInterface
    {
        return $this->namespaceRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getResourceRegistry(): ResourceRegistryInterface
    {
        return $this->resourceRegistry;
    }

    /**
     * @inheritDoc
     */
    public function setNamespace(AclSubjectInterface $subject, string $namespace, bool $value): bool
    {
        $nCfg = $this->namespaceRegistry->find($namespace);

        $changed = false;

        foreach ($this->resourceRegistry->all() as $rCfg) {
            if ($rCfg->getNamespace() !== $nCfg->getName()) {
                continue;
            }

            if ($this->setResource($subject, $namespace, $rCfg->getName(), $value)) {
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function setResource(AclSubjectInterface $subject, string $namespace, string $resource, bool $value): bool
    {
        $config = $this->resourceRegistry->find($namespace . '.' . $resource);

        if (empty($perms = $this->resolvePermissions($config))) {
            return false;
        }

        $changed = false;

        foreach ($perms as $name => $label) {
            if ($this->setPermission($subject, $namespace, $resource, $name, $value)) {
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function setPermission(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name,
        bool $value
    ): bool {
        $inherited = $this->getInherited($subject, $namespace, $resource, $name);

        $ace = $this->repository->findAce($subject, $namespace, $resource, $name);

        // Change to granted
        if ($value) {
            // If no inheritance
            if (null === $inherited) {
                // If current is not granted
                if (true !== $this->getValue($subject, $namespace, $resource, $name)) {
                    // Create ace if not exists
                    if (!$ace) {
                        $ace = $this->createAce($subject, $namespace, $resource, $name);
                    }

                    // Grant access
                    $ace->setGranted(true);
                    $this->wrapped->persist($ace);

                    $this->setCache($subject, $namespace, $resource, $name, true);

                    return true;
                }
            } // If inherited is granted
            elseif (true === $inherited) {
                // Remove ace if exists to inherit
                if ($ace) {
                    $this->wrapped->remove($ace);

                    $this->setCache($subject, $namespace, $resource, $name);

                    return true;
                }
            }

            // Any other case: nothing to do / grant access is denied
            return false;
        }

        // Change to denied
        // If inherited is granted
        if (true === $inherited) {
            // Create ace if not exists
            if (!$ace) {
                $ace = $this->createAce($subject, $namespace, $resource, $name);
            }

            // Deny access
            $ace->setGranted(false);
            $this->wrapped->persist($ace);

            $this->setCache($subject, $namespace, $resource, $name, false);

            return true;
        } // Any other case: remove ACE if exists to deny access
        elseif ($ace) {
            $this->wrapped->remove($ace);

            $this->setCache($subject, $namespace, $resource, $name);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        $this->wrapped->flush();
    }

    /**
     * @inheritDoc
     */
    public function getAcl(AclSubjectInterface $subject): array
    {
        $namespaces = [];
        foreach ($this->namespaceRegistry->all() as $nCfg) {
            if (null === $namespace = $this->getNamespace($subject, $nCfg)) {
                continue;
            }

            $namespaces[$nCfg->getName()] = $namespace;
        }

        return [
            'inheritance' => null !== $subject->getAclParentSubject(),
            'namespaces'  => $namespaces,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNamespace(AclSubjectInterface $subject, NamespaceConfig $config): ?array
    {
        $resources = [];
        foreach ($this->resourceRegistry->all() as $rCfg) {
            if ($rCfg->getNamespace() !== $config->getName()) {
                continue;
            }

            if (null === $resource = $this->getResource($subject, $rCfg)) {
                continue;
            }

            $resources[] = $resource;
        }
        if (empty($resources)) {
            return null;
        }

        return [
            'name'         => $config->getName(),
            'label'        => $config->getLabel(),
            'trans_domain' => $config->getTransDomain(),
            'resources'    => $resources,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResource(AclSubjectInterface $subject, ResourceConfig $config): ?array
    {
        if (empty($names = $this->resolvePermissions($config))) {
            return null;
        }

        $permissions = [];
        foreach ($names as $name => $permission) {
            $permissions[] = array_replace(
                $permission,
                $this->getPermission($subject, $config->getNamespace(), $config->getName(), $name)
            );
        }

        return [
            'name'         => $config->getName(),
            'label'        => $config->getResourceLabel(),
            'trans_domain' => $config->getTransDomain(),
            'permissions'  => $permissions,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPermission(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name
    ): array {
        $value = $this->getValue($subject, $namespace, $resource, $name);
        $inherited = $this->getInherited($subject, $namespace, $resource, $name);

        return [
            'name'      => $name,
            'granted'   => $value ?? $inherited ?? false,
            'value'     => $value,
            'inherited' => $inherited,
        ];
    }

    /**
     * Returns whether the given subject has access granted for the given resource and permission.
     *
     * @param AclSubjectInterface      $subject
     * @param ResourceInterface|string $resource
     * @param string                   $permission
     *
     * @return bool
     */
    public function isGranted(AclSubjectInterface $subject, $resource, string $permission): bool
    {
        $config = $this->resourceRegistry->find($resource);

        return $this->getValue($subject, $config->getNamespace(), $config->getName(), $permission)
            ?? $this->getInherited($subject, $config->getNamespace(), $config->getName(), $permission)
            ?? false;
    }

    /**
     * Returns the access control list for the given subject.
     *
     * @param AclSubjectInterface $subject
     *
     * @return array
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function loadAcl(AclSubjectInterface $subject): array
    {
        $id = $subject->getAclSubjectId();

        if (isset($this->aclCache[$id])) {
            return $this->aclCache[$id];
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->itemCache->getItem($id);
        if ($item->isHit()) {
            return $item->get();
        }

        $acl = $this->aclCache[$id] = $this->repository->findAcl($subject);

        $item->set($acl);
        $this->itemCache->save($item);

        return $acl;
    }

    /**
     * Changes the cached permission value.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $name
     * @param null|bool           $value
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function setCache(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name,
        bool $value = null
    ) {
        $id = $subject->getAclSubjectId();

        /** @noinspection PhpUnhandledExceptionInspection */
        $item = $this->itemCache->getItem($id);

        if (isset($this->aclCache[$id])) {
            $acl = $this->aclCache[$id];
        } elseif ($item->isHit()) {
            $acl = $item->get();
        } else {
            return; // Abort if not cached
        }

        // If set to inherit
        if (null === $value) {
            // Clear cache entry
            unset($acl[$namespace][$resource][$name]);
        } else {
            // Set value
            $acl[$namespace][$resource][$name] = $value;
        }

        $item->set($acl);
        $this->itemCache->save($item);

        $this->aclCache[$id] = $acl;
    }

    /**
     * Returns the value for the given permission.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $name
     *
     * @return bool|null
     */
    private function getValue(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name
    ): ?bool {
        $acl = $this->loadAcl($subject);

        if (isset($acl[$namespace][$resource][$name])) {
            return $acl[$namespace][$resource][$name];
        }

        return null;
    }

    /**
     * Returns the inherited value for the given permission.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $name
     *
     * @return bool|null
     */
    private function getInherited(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $name
    ): ?bool {
        $parent = $subject;
        while ($parent = $parent->getAclParentSubject()) {
            if (null !== $value = $this->getValue($parent, $namespace, $resource, $name)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Resolves the resource permissions.
     *
     * @param ResourceConfig $rConfig
     *
     * @return array
     */
    private function resolvePermissions(ResourceConfig $rConfig): array
    {
        $permissions = [];

        foreach ($rConfig->getPermissions() as $pName) {
            $pConfig = $this->permissionRegistry->find($pName);

            $permissions[$pName] = [
                'label'        => $pConfig->getLabel(),
                'trans_domain' => $pConfig->getTransDomain(),
            ];
        }

        return $permissions;
    }

    /**
     * Creates a new access control entry.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $permission
     *
     * @return AccessControlEntry
     */
    private function createAce(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $permission
    ): AccessControlEntry {
        $ace = new AccessControlEntry();

        return $ace
            ->setSubject($subject->getAclSubjectId())
            ->setNamespace($namespace)
            ->setResource($resource)
            ->setPermission($permission);
    }
}
