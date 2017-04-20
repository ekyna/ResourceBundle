<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Trait TemplatingTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TemplatingTrait
{
    private Environment $twig;


    /**
     * Sets the twig environment.
     *
     * @param Environment $twig
     *
     * @required
     */
    public function setEnvironment(Environment $twig): void
    {
        $this->twig = $twig;
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view       The view name
     * @param array  $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->twig->render($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param string        $view       The view name
     * @param array         $parameters An array of parameters to pass to the view
     * @param Response|null $response   A response instance
     *
     * @return Response A Response instance
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->renderView($view, $parameters));

        return $response;
    }
}
