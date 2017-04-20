<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing;

use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Config;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ResourceLoader
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceLoader extends Loader
{
    private string $name;
    private string $interface;
    private string $prefix;
    private string $defaultLocale;

    private Config\Registry\NamespaceRegistryInterface $namespaceRegistry;
    private Config\Registry\ResourceRegistryInterface  $resourceRegistry;
    private Config\Registry\ActionRegistryInterface    $actionRegistry;

    private bool $loaded = false;


    public function __construct(
        string $name,
        string $interface,
        string $prefix,
        string $defaultLocale = 'en',
        string $env = null
    ) {
        parent::__construct($env);

        $this->name = $name;
        $this->interface = $interface;
        $this->prefix = $prefix;
        $this->defaultLocale = $defaultLocale;
    }

    public function setNamespaceRegistry(Config\Registry\NamespaceRegistryInterface $namespaceRegistry): void
    {
        $this->namespaceRegistry = $namespaceRegistry;
    }

    public function setResourceRegistry(Config\Registry\ResourceRegistryInterface $resourceRegistry): void
    {
        $this->resourceRegistry = $resourceRegistry;
    }

    public function setActionRegistry(Config\Registry\ActionRegistryInterface $actionRegistry): void
    {
        $this->actionRegistry = $actionRegistry;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new RuntimeException("Do not add the '$this->name' routes loader twice.");
        }

        $this->loaded = true;

        $collection = new RouteCollection();

        foreach ($this->namespaceRegistry->all() as $namespace) {
            $collection->addCollection($this->loadNamespaceRoutes($namespace));
        }

        $collection->addPrefix($this->prefix);
        // Set default locale
        $collection->addDefaults([
            '_locale' => $this->defaultLocale,
        ]);
        // Disable internationalization
        $collection->addOptions([
            'i18n' => false,
        ]);

        return $collection;
    }

    private function loadNamespaceRoutes(Config\NamespaceConfig $namespace): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->resourceRegistry->all() as $resource) {
            // Skip children
            if (null !== $resource->getParentId()) {
                continue;
            }

            if ($resource->getNamespace() !== $namespace->getName()) {
                continue;
            }

            $collection->addCollection($this->loadResourceRoutes($resource));
        }

        $collection->addPrefix($namespace->getPrefix());

        return $collection;
    }

    /**
     * @see \Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingExtension
     */
    private function loadResourceRoutes(Config\ResourceConfig $resource): RouteCollection
    {
        $collection = new RouteCollection();
        $parameter = RoutingUtil::getRouteParameter($resource);

        foreach (array_keys($resource->getActions()) as $name) {
            $action = $this->actionRegistry->find($name);

            $class = $action->getClass();

            if (!is_a($class, $this->interface, true)) {
                continue;
            }

            if (!$config = $action->getData('route')) {
                continue;
            }

            $options = array_replace_recursive(
                $action->getDefaultOptions(),
                $resource->getAction($action->getClass())
            );

            if (!empty($path = trim($config['path'], '/'))) {
                $path = "/$path";
            }

            $requirements = [];
            if ($config['resource']) {
                $path = sprintf('/{%s}%s', $parameter, $path);
                $requirements[$parameter] = '\d+';
            }

            $route = new Route($path);
            $route
                ->setDefaults([
                    '_controller' => $class,
                    '_resource'   => $resource->getId(),
                ])
                ->setMethods($config['methods'])
                ->setRequirements($requirements);

            // Expose option (for JS router)
            if ($options['expose']) {
                $route->setOption('expose', true);
            }

            // Custom route builder
            if (is_a($class, RoutingActionInterface::class, true)) {
                /** @see RoutingActionInterface::buildRoute() */
                call_user_func([$class, 'buildRoute'], $route, $options);
            }

            $collection->add(RoutingUtil::getRouteName($resource, $action), $route);
        }

        foreach ($resource->getChildren() as $child) {
            $childCollection = $this->loadResourceRoutes($child);

            $childCollection->addPrefix(sprintf('/{%s}', $parameter), [], [$parameter => '\d+']);

            $collection->addCollection($childCollection);
        }

        $collection->addPrefix('/' . str_replace('_', '-', $resource->getName()));

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null): bool
    {
        return $this->name === $type;
    }
}
