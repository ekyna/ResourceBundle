<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Trait MessengerTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait MessengerTrait
{
    protected ?MessageBusInterface $messageBus = null;

    public function setMessageBus(?MessageBusInterface $messageBus): void
    {
        $this->messageBus = $messageBus;
    }
}
