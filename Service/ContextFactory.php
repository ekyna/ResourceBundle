<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service;

use Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingUtil;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ContextFactory
 * @package Ekyna\Bundle\ResourceBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ContextFactory
{
    private RequestStack $requestStack;
    private ResourceRegistryInterface $resourceRegistry;
    private RepositoryFactoryInterface $repositoryFactory;
    private array $contexts = [];


    public function __construct(
        RequestStack $requestStack,
        ResourceRegistryInterface $resourceRegistry,
        RepositoryFactoryInterface $repositoryFactory
    ) {
        $this->requestStack = $requestStack;
        $this->resourceRegistry = $resourceRegistry;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Returns the context for the given resource.
     */
    public function getContext(ResourceConfig|string $config): Context
    {
        if (is_string($config)) {
            $config = $this->resourceRegistry->find($config);
        }

        if (!$config instanceof ResourceConfig) {
            throw new UnexpectedTypeException($config, ['string', ResourceConfig::class]);
        }

        if (isset($this->contexts[$config->getId()])) {
            return $this->contexts[$config->getId()];
        }

        $request = $this->requestStack->getMainRequest();

        $context = new Context($config);

        $this->contexts[$config->getId()] = $context;

        if ($parentId = $config->getParentId()) {
            $context->setParent($this->getContext($parentId));
        }

        $parameter = RoutingUtil::getRouteParameter($config);
        if (!$request->attributes->has($parameter)) {
            return $context;
        }

        if (0 < $id = $request->attributes->getInt($parameter)) {
            $resource = $this
                ->repositoryFactory
                ->getRepository($config->getEntityClass())
                ->find($id);

            if (!$resource) {
                throw new NotFoundHttpException('Resource not found.');
            }

            $context->setResource($resource);
        }

        return $context;
    }
}
