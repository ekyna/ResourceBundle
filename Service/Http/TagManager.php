<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagManager
 * @package Ekyna\Bundle\ResourceBundle\Service\Http
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TagManager
{
    /**
     * Configures the response.
     *
     * @param Response $response
     * @param array    $tags
     * @param int|null $sMaxAge
     */
    public function configureResponse(Response $response, array $tags = [], int $sMaxAge = null): void
    {
    }

    /**
     * Invalidates tags.
     *
     * @param mixed $tags
     */
    public function invalidateTags($tags): void
    {
    }

    /**
     * Adds tags.
     *
     * @param mixed $tags
     */
    public function addTags($tags): void
    {
    }

    /**
     * Flushes the tag manager.
     */
    public function flush(): void
    {
    }
}
