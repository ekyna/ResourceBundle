<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Helper;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\ResourceBundle\Service\ContextFactory;
use Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingUtil;
use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_key_exists;
use function get_class;
use function implode;
use function is_null;
use function sprintf;

/**
 * Class ResourceHelper
 * @package Ekyna\Bundle\AdminBundle\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class ResourceHelper
{
    private ActionRegistryInterface          $actionRegistry;
    private ResourceRegistryInterface        $resourceRegistry;
    private ManagerFactoryInterface          $managerFactory;
    private ResourceEventDispatcherInterface $dispatcher;
    private ContextFactory                   $contextFactory;
    private AuthorizationCheckerInterface    $authorization;
    private RouterInterface                  $router;

    private ?PropertyAccessorInterface $accessor = null;


    public function __construct(
        ActionRegistryInterface $actionRegistry,
        ResourceRegistryInterface $resourceRegistry,
        ManagerFactoryInterface $managerFactory,
        ResourceEventDispatcherInterface $dispatcher,
        ContextFactory $contextFactory,
        AuthorizationCheckerInterface $authorization,
        RouterInterface $router
    ) {
        $this->actionRegistry = $actionRegistry;
        $this->resourceRegistry = $resourceRegistry;
        $this->managerFactory = $managerFactory;
        $this->dispatcher = $dispatcher;
        $this->contextFactory = $contextFactory;
        $this->authorization = $authorization;
        $this->router = $router;
    }

    /**
     * Returns the action config for the given name.
     */
    public function getActionConfig(string $action): ActionConfig
    {
        return $this->actionRegistry->find($action);
    }

    /**
     * Returns the configuration for the resource.
     */
    public function getResourceConfig(ResourceInterface|string $resource): ResourceConfig
    {
        return $this->resourceRegistry->find($resource);
    }

    public function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->router;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     */
    public function isGranted(string $attribute, $resource = null): bool
    {
        return $this->authorization->isGranted($attribute, $resource);
    }

    /**
     * Generates an admin path for the given resource and action.
     */
    public function generateResourcePath(
        string|ResourceInterface $resource,
        string $action,
        array $parameters = [],
        bool $absolute = false
    ): string {
        $routeName = $this->getRoute($resource, $action);

        $config = $this->resourceRegistry->find($resource);

        $route = $this->findRoute($routeName);
        $requirements = $route->getRequirements();

        if ($resource instanceof ResourceInterface) {
            /** @var ResourceInterface $resource */
            $parameter = RoutingUtil::getRouteParameter($config);
            if (!isset($parameters[$parameter]) && array_key_exists($parameter, $requirements)) {
                $parameters[$parameter] = $resource->getId();
            }
        } else {
            $resource = null;
        }

        while (null !== $parentId = $config->getParentId()) {
            $parentConfig = $this->resourceRegistry->find($parentId);
            $parameter = RoutingUtil::getRouteParameter($parentConfig);

            if (isset($parameters[$parameter]) || !array_key_exists($parameter, $requirements)) {
                break;
            }

            if (!$parent = $this->getParentResource($parentConfig, $resource)) {
                break;
            }

            $parameters[$parameter] = $parent->getId();

            $resource = $parent;
            $config = $parentConfig;
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->router->generate($routeName, $parameters, $type);
    }

    /**
     * Returns the public url for the given resource.
     */
    public function generatePublicUrl(
        ResourceInterface $resource,
        bool $absolute = false,
        string $locale = null
    ): ?string {
        return $this->generateUrl($resource, 'public_url', $absolute, $locale);
    }

    /**
     * Returns the image url for the given resource.
     */
    public function generateImageUrl(ResourceInterface $resource, bool $absolute = false): ?string
    {
        return $this->generateUrl($resource, 'image_url', $absolute);
    }

    protected function generateUrl(
        ResourceInterface $resource,
        string $name,
        bool $absolute = true,
        string $locale = null
    ): ?string {
        if (null === $event = $this->dispatcher->createResourceEvent($resource, false)) {
            return null;
        }

        $event->addData('_locale', $locale);

        $name = $this->dispatcher->getResourceEventName($resource, $name);

        $this->dispatcher->dispatch($event, $name);

        if (!$event->hasData('route')) {
            return null;
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        $parameters = $event->hasData('parameters') ? $event->getData('parameters') : [];

        if ($locale) {
            $parameters['_locale'] = $locale;
        }

        try {
            return $this->router->generate($event->getData('route'), $parameters, $type);
        } catch (RoutingException $exception) {
            return null;
        }
    }

    /**
     * Returns the route name for the given resource and action.
     */
    public function getRoute(ResourceInterface|string $resource, string $action): string
    {
        $aCfg = $this->actionRegistry->find($action);

        $rCfg = $this->resourceRegistry->find($resource);

        if (null === $rCfg->getAction($aCfg->getClass())) {
            throw new LogicException(sprintf('Resource %s has no action named %s.', $rCfg->getId(), $action));
        }

        return RoutingUtil::getRouteName($rCfg, $aCfg);
    }

    /**
     * Builds routing parameters map.
     */
    public function buildParametersMap(ResourceConfig $resource, bool $include = true): array
    {
        $map = $parents = [];

        if ($include) {
            $map[RoutingUtil::getRouteParameter($resource)] = 'id';
        }

        while ($parentId = $resource->getParentId()) {
            $parent = $this->resourceRegistry->find($parentId);

            // TODO Use a metadata registry
            $metadata = $this->getManager($resource->getEntityClass())->getMetadata();
            if ($metadata instanceof ClassMetadata) {
                $associations = $metadata->getAssociationsByTargetClass($parent->getEntityClass());

                foreach ($associations as $mapping) {
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                        $parents[] = $mapping['fieldName'];
                        $map[RoutingUtil::getRouteParameter($parent)] = implode('.', $parents) . '.id';

                        break;
                    }
                }
            } // TODO ODM metadata case

            $resource = $parent;
        }

        return $map;
    }

    /**
     * Finds the route definition.
     */
    private function findRoute(string $routeName): ?Route
    {
        $route = $this->router->getRouteCollection()->get($routeName);

        if (is_null($route)) {
            throw new RouteNotFoundException(sprintf('Route "%s" not found.', $routeName));
        }

        return $route;
    }

    private function getParentResource(ResourceConfig $parentConfig, ?ResourceInterface $resource): ?ResourceInterface
    {
        if ($resource) {
            $accessor = $this->getAccessor();

            // By getter
            $property = $parentConfig->getCamelCaseName();
            if ($accessor->isReadable($resource, $property)) {
                if ($parent = $accessor->getValue($resource, $property)) {
                    return $parent;
                }
            }

            // By metadata association mapping
            // TODO Use a metadata registry
            $metadata = $this->getManager(get_class($resource))->getMetadata();
            if ($metadata instanceof ClassMetadata) {
                $associations = $metadata->getAssociationsByTargetClass($parentConfig->getEntityClass());

                foreach ($associations as $mapping) {
                    if ($mapping['type'] !== ClassMetadataInfo::MANY_TO_ONE) {
                        continue;
                    }

                    if (!$accessor->isReadable($resource, $mapping['fieldName'])) {
                        continue;
                    }

                    if ($parent = $accessor->getValue($resource, $mapping['fieldName'])) {
                        return $parent;
                    }
                }
            }
        }

        // By action context
        // TODO Only for create actions ?
        $context = $this->contextFactory->getContext($parentConfig);
        if ($parent = $context->getResource()) {
            return $parent;
        }

        return null;
    }

    private function getManager(string $class): ResourceManagerInterface
    {
        return $this->managerFactory->getManager($class);
    }

    private function getAccessor(): PropertyAccessorInterface
    {
        if ($this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
