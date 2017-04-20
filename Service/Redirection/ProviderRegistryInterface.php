<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Redirection;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;

/**
 * Interface ProviderRegistryInterface
 * @package Ekyna\Bundle\ResourceBundle\Service\Redirection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ProviderRegistryInterface
{
    /**
     * Registers the provider.
     *
     * @param ProviderInterface $provider
     *
     * @throws InvalidArgumentException
     */
    public function addProvider(ProviderInterface $provider): void;

    /**
     * Returns the registered providers.
     *
     * @return ProviderInterface[]
     */
    public function getProviders(): array;
}
