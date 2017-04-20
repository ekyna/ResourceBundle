<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;

/**
 * Class ResourceEventDispatcherTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ResourceEventDispatcherTrait
{
    private ResourceEventDispatcherInterface $resourceEventDispatcher;

    /**
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setResourceEventDispatcher(ResourceEventDispatcherInterface $dispatcher): void
    {
        $this->resourceEventDispatcher = $dispatcher;
    }

    /**
     * Returns the resource event dispatcher.
     *
     * @return ResourceEventDispatcherInterface
     */
    protected function getResourceEventDispatcher(): ResourceEventDispatcherInterface
    {
        return $this->resourceEventDispatcher;
    }
}
