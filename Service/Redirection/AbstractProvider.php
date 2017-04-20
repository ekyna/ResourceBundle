<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Redirection;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractProvider
 * @package Ekyna\Bundle\ResourceBundle\Service\Redirection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return !empty($request->getPathInfo());
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 0;
    }
}
