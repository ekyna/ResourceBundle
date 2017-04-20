<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\Routing\Route;

/**
 * Interface RoutingActionInterface
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RoutingActionInterface
{
    /**
     * Customize the generated route.
     *
     * @param Route $route
     * @param array $options
     */
    public static function buildRoute(Route $route, array $options): void;
}
