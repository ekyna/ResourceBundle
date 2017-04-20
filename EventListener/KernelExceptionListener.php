<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\EventListener;

use Ekyna\Bundle\ResourceBundle\Exception\RedirectException;
use Ekyna\Bundle\ResourceBundle\Service\Error\ErrorReporter;
use Ekyna\Bundle\ResourceBundle\Service\Redirection\ProviderRegistryInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\HttpUtils;

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
    private ErrorReporter $errorReporter;
    private RequestStack  $requestStack;
    private bool          $debug;


    public function __construct(
        ProviderRegistryInterface $registry,
        HttpUtils $utils,
        ErrorReporter $errorReporter,
        RequestStack  $requestStack,
        bool $debug
    ) {
        $this->registry = $registry;
        $this->utils = $utils;
        $this->errorReporter = $errorReporter;
        $this->requestStack = $requestStack;
        $this->debug = $debug;
    }

    /**
     * Kernel exception event handler.
     *
     * @param ExceptionEvent $event
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $this->handleNotFoundHttpException($event);

            return;
        }

        if ($exception instanceof AccessDeniedException) {
            if ($event->getRequest()->isXmlHttpRequest()) {
                $event->setResponse(new Response('', Response::HTTP_FORBIDDEN));
            }

            return;
        }

        if ($exception instanceof RedirectException) {
            $this->handleRedirectionException($event);

            return;
        }

        if ($exception instanceof HttpException) {
            // Don't send log about others http exceptions.
            return;
        }

        if ($this->debug) {
            return;
        }

        $this->errorReporter->report($exception, $event->getRequest());
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
