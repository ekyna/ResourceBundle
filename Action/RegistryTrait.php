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
     * @required
     */
    public function setRegistryFactory(RegistryFactory $registryFactory): void
    {
        $this->registryFactory = $registryFactory;
    }

    protected function getActionRegistry(): Registry\ActionRegistryInterface
    {
        return $this->registryFactory->getActionRegistry();
    }

    protected function getBehaviorRegistry(): Registry\BehaviorRegistryInterface
    {
        return $this->registryFactory->getBehaviorRegistry();
    }

    protected function getNamespaceRegistry(): Registry\NamespaceRegistryInterface
    {
        return $this->registryFactory->getNamespaceRegistry();
    }

    protected function getPermissionRegistry(): Registry\PermissionRegistryInterface
    {
        return $this->registryFactory->getPermissionRegistry();
    }

    protected function getResourceRegistry(): Registry\ResourceRegistryInterface
    {
        return $this->registryFactory->getResourceRegistry();
    }
}
