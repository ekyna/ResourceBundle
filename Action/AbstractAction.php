<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\AbstractAction as BaseAction;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AbstractAction
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAction extends BaseAction
{
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Redirects to the referer.
     *
     * @param string $fallback
     *
     * @return RedirectResponse
     */
    protected function redirectToReferer(string $fallback): RedirectResponse
    {
        if ($redirect = $this->getRedirect()) {
            return $this->redirect($redirect);
        }

        if ($referer = $this->getReferer()) {
            return $this->redirect($referer);
        }

        return $this->redirect($fallback);
    }

    /**
     * Returns the redirect url (from '_redirect' query parameters).
     *
     * @return string|null
     */
    private function getRedirect(): ?string
    {
        if (empty($redirect = $this->request->query->get('_redirect'))) {
            return null;
        }

        return $redirect;
    }

    /**
     * Returns the referer url.
     *
     * @return string|null
     */
    private function getReferer(): ?string
    {
        if (empty($referer = $this->request->headers->get('referer'))) {
            return null;
        }

        return $referer;
    }
}
