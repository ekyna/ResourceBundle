<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\EventListener;

use Ekyna\Bundle\ResourceBundle\Exception\RedirectException;
use Ekyna\Bundle\ResourceBundle\Service\Redirection\ProviderRegistryInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception as Http;
use Symfony\Component\Security\Core\Exception as Security;
use Symfony\Component\Security\Http\HttpUtils;
use Twig\Error\RuntimeError;

use function is_string;
use function preg_match;

/**
 * Class KernelExceptionListener
 * @package Ekyna\Bundle\ResourceBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class KernelExceptionListener
{
    private ProviderRegistryInterface $registry;
    private HttpUtils                 $utils;
    private RequestStack              $requestStack;

    public function __construct(
        ProviderRegistryInterface $registry,
        HttpUtils                 $utils,
        RequestStack              $requestStack
    ) {
        $this->registry = $registry;
        $this->utils = $utils;
        $this->requestStack = $requestStack;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Redirection exception thrown from twig template rendering
        if ($exception instanceof RuntimeError) {
            $previous = $exception->getPrevious();
            if (!$previous instanceof RedirectException) {
                return;
            }
            $exception = $previous;
            $event->setThrowable($exception);
        }

        if ($exception instanceof Http\NotFoundHttpException) {
            $this->handleNotFoundHttpException($event);

            return;
        }

        if ($exception instanceof RedirectException) {
            $this->handleRedirectionException($event);

            return;
        }

        if (!$exception instanceof Security\AccessDeniedException) {
            return;
        }

        if ($event->getRequest()->isXmlHttpRequest()) {
            $event->setResponse(new Response('', Response::HTTP_FORBIDDEN));
        }
    }

    private function handleNotFoundHttpException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Skip admin / api paths
        if (preg_match('~^/(admin|api)~', $request->getPathInfo())) {
            return;
        }

        foreach ($this->registry->getProviders() as $provider) {
            if (!$provider->supports($request)) {
                continue;
            }

            if (false === $response = $provider->redirect($request)) {
                continue;
            }

            if ($response instanceof RedirectResponse) {
                $event->setResponse($response);

                return;
            }

            if (is_string($response) && !empty($response)) {
                $response = $this
                    ->utils
                    ->createRedirectResponse($request, $response, Response::HTTP_MOVED_PERMANENTLY);

                $event->setResponse($response);
            }

            throw new UnexpectedTypeException($response, [RedirectResponse::class, 'string']);
        }
    }

    private function handleRedirectionException(ExceptionEvent $event): void
    {
        /** @var RedirectException $exception */
        $exception = $event->getThrowable();

        // Check path
        if (empty($path = $exception->getPath())) {
            return;
        }

        $request = $event->getRequest();

        // Build the response
        $event->setResponse(
            $this->utils->createRedirectResponse($request, $path)
        );

        // Add flash
        if (empty($message = $exception->getMessage())) {
            return;
        }

        try {
            $this->requestStack->getSession()->getFlashBag()->add($exception->getMessageType(), $message);
        } catch (SessionNotFoundException $exception) {
        }
    }
}
