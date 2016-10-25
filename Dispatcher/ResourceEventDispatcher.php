<?php

namespace Ekyna\Bundle\ResourceBundle\Dispatcher;

use Ekyna\Component\Resource\Dispatcher as RD;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Class ResourceEventDispatcher
 * @package Ekyna\Bundle\ResourceBundle\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEventDispatcher extends ContainerAwareEventDispatcher implements RD\ResourceEventDispatcherInterface
{
    use RD\ResourceEventDispatcherTrait;
}
