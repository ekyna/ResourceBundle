<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing\Traits;

use InvalidArgumentException;
use Symfony\Component\Routing\RouteCollection;

use function preg_quote;
use function sprintf;
use function trim;

/**
 * Trait PrefixTrait
 * @package Ekyna\Bundle\ResourceBundle\Service\Routing\Traits
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait PrefixTrait
{
    /**
     * @see \Symfony\Component\Routing\Loader\Configurator\Traits\PrefixTrait::addPrefix
     */
    protected function addPrefixes(RouteCollection $routes, array $prefixes): void
    {
        foreach ($prefixes as $locale => $localePrefix) {
            $prefix[$locale] = trim(trim($localePrefix), '/');
        }

        foreach ($routes->all() as $name => $route) {
            if (null === $locale = $route->getDefault('_locale')) {
                $routes->remove($name);
                foreach ($prefix as $locale => $localePrefix) {
                    $localizedRoute = clone $route;
                    $localizedRoute->setDefault('_locale', $locale);
                    $localizedRoute->setRequirement('_locale', preg_quote($locale));
                    $localizedRoute->setDefault('_canonical_route', $name);
                    $localizedRoute->setPath($localePrefix . ('/' === $route->getPath() ? '' : $route->getPath()));
                    $routes->add($name . '.' . $locale, $localizedRoute);
                }
            } elseif (!isset($prefix[$locale])) {
                throw new InvalidArgumentException(sprintf('Route "%s" with locale "%s" is missing a corresponding prefix in its parent collection.', $name, $locale));
            } else {
                $route->setPath($prefix[$locale] . ('/' === $route->getPath() ? '' : $route->getPath()));
                $routes->add($name, $route);
            }
        }
    }
}
