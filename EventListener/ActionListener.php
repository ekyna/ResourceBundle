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
    private ActionRegistryInterface       $actionRegistry;
    private ContextFactory                $contextFactory;
    private AuthorizationCheckerInterface $authorization;


    public function __construct(
        ActionRegistryInterface $actionRegistry,
        ContextFactory $contextFactory,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->actionRegistry = $actionRegistry;
        $this->contextFactory = $contextFactory;
        $this->authorization = $authorization;
    }

    /**
     * Kernel controller event handler.
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $action = $event->getController();

        if (!$action instanceof Action\ActionInterface) {
            return;
        }

        $config = $this->actionRegistry->find(get_class($action));

        $request = $event->getRequest();

        $context = $this
            ->contextFactory
            ->getContext($request->attributes->get('_resource'));

        if ($permission = $config->getPermission()) {
            if (!$resource = $context->getResource()) {
                $resource = $context->getConfig()->getId();
            }

            if (!$this->authorization->isGranted($permission, $resource)) {
                throw new AccessDeniedHttpException();
            }
        }

        $action
            ->setConfig($config)
            ->setRequest($request)
            ->setContext($context)
            ->setOptions(array_replace_recursive(
                $config->getDefaultOptions(),
                $context->getConfig()->getAction($config->getClass())
            ));
    }
}
