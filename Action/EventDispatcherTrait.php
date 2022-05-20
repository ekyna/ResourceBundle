<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Trait EventDispatcherTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait EventDispatcherTrait
{
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @see \Symfony\Contracts\EventDispatcher\EventDispatcherInterface::dispatch
     */
    protected function dispatch(object $event, string $eventName = null): object
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
