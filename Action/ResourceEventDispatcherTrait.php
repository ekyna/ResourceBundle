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
     * @required
     */
    public function setResourceEventDispatcher(ResourceEventDispatcherInterface $dispatcher): void
    {
        $this->resourceEventDispatcher = $dispatcher;
    }

    protected function getResourceEventDispatcher(): ResourceEventDispatcherInterface
    {
        return $this->resourceEventDispatcher;
    }
}
