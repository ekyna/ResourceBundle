<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\Context;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Trait AuthorizationTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait AuthorizationTrait
{
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @required
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $checker): void
    {
        $this->authorizationChecker = $checker;
    }

    protected function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param mixed $attributes The attributes
     * @param mixed $subject    The subject (defaults to current resource)
     *
     * @return bool
     */
    protected function isGranted($attributes = null, $subject = null): bool
    {
        if (null === $subject) {
            if (null === $subject = $this->context->getResource()) {
                $subject = $this->context->getConfig()->getId();
            }
        }

        return $this->authorizationChecker->isGranted($attributes, $subject);
    }

    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied subject.
     *
     * @param mixed  $attributes The attributes
     * @param mixed  $subject    The subject
     * @param string $message    The message passed to the exception
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void
    {
        if ($this->isGranted($attributes, $subject)) {
            return;
        }

        $exception = new AccessDeniedException($message);
        $exception->setAttributes($attributes);
        $exception->setSubject($subject);

        throw $exception;
    }
}
