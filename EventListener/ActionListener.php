<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\EventListener;

use Ekyna\Bundle\ResourceBundle\Service\ContextFactory;
use Ekyna\Component\Resource\Action;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ActionListener
 * @package Ekyna\Bundle\ResourceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ActionListener
{
    public function __construct(
        private readonly ActionRegistryInterface       $actionRegistry,
        private readonly ContextFactory                $contextFactory,
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    /**
     * Kernel controller event handler.
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!$controller instanceof Action\ActionInterface) {
            return;
        }

        $config = $this->actionRegistry->find(get_class($controller));

        $request = $event->getRequest();

        $context = $this
            ->contextFactory
            ->getContext($request->attributes->get('_resource'));

        if (null === $resource = $context->getResource()) {
            $resource = $context->getConfig()->getId();
        }

        if (!$this->authorization->isGranted($config->getName(), $resource)) {
            throw new AccessDeniedHttpException();
        }

        $controller
            ->setConfig($config)
            ->setRequest($request)
            ->setContext($context)
            ->setOptions(
                array_replace_recursive(
                    $config->getDefaultOptions(),
                    $context->getConfig()->getAction($config->getClass())
                )
            );
    }
}
