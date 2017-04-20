<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Config\Factory\RegistryFactory;
use Ekyna\Component\Resource\Config\Registry;

/**
 * Class RegistryTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait RegistryTrait
{
    private RegistryFactory $registryFactory;


    /**
     * Sets the registry factory.
     *
     * @param RegistryFactory $registryFactory
     *
     * @required
     */
    public function setRegistryFactory(RegistryFactory $registryFactory): void
    {
        $this->registryFactory = $registryFactory;
    }

    /**
     * Returns the action config registry.
     *
     * @return Registry\ActionRegistryInterface
     */
    protected function getActionRegistry(): Registry\ActionRegistryInterface
    {
        return $this->registryFactory->getActionRegistry();
    }

    /**
     * Returns the behavior config registry.
     *
     * @return Registry\BehaviorRegistryInterface
     */
    protected function getBehaviorRegistry(): Registry\BehaviorRegistryInterface
    {
        return $this->registryFactory->getBehaviorRegistry();
    }

    /**
     * Returns the namespace config registry.
     *
     * @return Registry\NamespaceRegistryInterface
     */
    protected function getNamespaceRegistry(): Registry\NamespaceRegistryInterface
    {
        return $this->registryFactory->getNamespaceRegistry();
    }

    /**
     * Returns the permission config registry.
     *
     * @return Registry\PermissionRegistryInterface
     */
    protected function getPermissionRegistry(): Registry\PermissionRegistryInterface
    {
        return $this->registryFactory->getPermissionRegistry();
    }

    /**
     * Returns the resource config registry.
     *
     * @return Registry\ResourceRegistryInterface
     */
    protected function getResourceRegistry(): Registry\ResourceRegistryInterface
    {
        return $this->registryFactory->getResourceRegistry();
    }
}
