<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\ResourceConfig;

/**
 * Class RoutingUtil
 * @package Ekyna\Bundle\ResourceBundle\Service\Routing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RoutingUtil
{
    /**
     * Returns the route name for the given resource and action.
     *
     * @param ResourceConfig $resource
     * @param ActionConfig   $action
     *
     * @return string|null
     */
    public static function getRouteName(ResourceConfig $resource, ActionConfig $action): ?string
    {
        if (!$route = $action->getData('route')) {
            return null;
        }

        return sprintf($route['name'], $resource->getUnderscoreId());
    }

    /**
     * Returns the resource route parameter.
     *
     * @param ResourceConfig $config
     *
     * @return string
     */
    public static function getRouteParameter(ResourceConfig $config): string
    {
        return $config->getCamelCaseName() . 'Id';
    }
}
