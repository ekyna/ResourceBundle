<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Error;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;
use Twig\Environment;

use function sprintf;

/**
 * Class ErrorReporter
 * @package Ekyna\Bundle\ResourceBundle\Service\Error
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ErrorReporter
{
    private TokenStorageInterface $tokenStorage;
    private Environment           $twig;
    private MailerInterface       $mailer;
    private string                $reportEmail;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Environment           $twig,
        MailerInterface       $mailer,
        string                $reportEmail
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->reportEmail = $reportEmail;
    }

    public function report(Throwable $throwable, Request $request = null): void
    {
        $flatten = FlattenException::createFromThrowable($throwable);

        $stacks = $flatten->getAsString();
        $traces = $flatten->getTraceAsString();

        $subject = $request
            ? sprintf('[%s] Error report', $request->getHost())
            : 'Error report';

        $user = null;
        if ($token = $this->tokenStorage->getToken()) {
            $user = (string)$token->getUser();
        }

        $html = $this->twig->render('@EkynaResource/Exception/exception.html.twig', [
            'message' => $flatten->getMessage(),
            'code'    => $code = $flatten->getCode(),
            'status'  => Response::$statusTexts[$code] ?? '',
            'class'   => $flatten->getClass(),
            'request' => $request,
            'user'    => $user,
            'stacks'  => $stacks,
            'traces'  => $traces,
        ]);

        $report = new Email();
        $report
            ->from($this->reportEmail)
            ->to($this->reportEmail)
            ->subject($subject)
            ->html($html)
            // Try 'error' transport
            ->getHeaders()->addTextHeader('X-Transport', 'error');

        try {
            $this->mailer->send($report);

            return;
        } catch (Throwable $exception) {
        }

        // Fall back to default transport
        $report->getHeaders()->remove('X-Transport');

        try {
            $this->mailer->send($report);
        } catch (Throwable $exception) {
        }
    }
}
