<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Trait HelperTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait HelperTrait
{
    private ResourceHelper $resourceHelper;

    /**
     * @required
     */
    public function setResourceHelper(ResourceHelper $resourceHelper): void
    {
        $this->resourceHelper = $resourceHelper;
    }

    protected function getResourceHelper(): ResourceHelper
    {
        return $this->resourceHelper;
    }

    protected function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->resourceHelper->getUrlGenerator();
    }

    /**
     * Generates the resource path.
     *
     * @param object|string $resource
     * @param string        $action
     * @param array         $parameters
     *
     * @return string
     */
    protected function generateResourcePath($resource, $action = ReadAction::class, array $parameters = []): string
    {
        return $this->resourceHelper->generateResourcePath($resource, $action, $parameters);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route         The name of the route
     * @param array  $parameters    An array of parameters
     * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->getUrlGenerator()->generate($route, $parameters, $referenceType);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route      The name of the route
     * @param array  $parameters An array of parameters
     * @param int    $status     The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute(string $route, array $parameters = [], $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
}
