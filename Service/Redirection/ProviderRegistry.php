<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Redirection;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Class ProviderRegistry
 * @package Ekyna\Bundle\CoreBundle\Redirection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProviderRegistry implements ProviderRegistryInterface
{
    /** @var ProviderInterface[] */
    private array $providers;
    private bool  $initialized = false;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * @inheritDoc
     */
    public function addProvider(ProviderInterface $provider): void
    {
        if ($this->initialized) {
            throw new RuntimeException('Redirection registry as been initialized and can\'t register more providers.');
        }

        if (array_key_exists($provider->getName(), $this->providers)) {
            throw new InvalidArgumentException(sprintf('Provider "%s" is already registered.', $provider->getName()));
        }

        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * @inheritDoc
     */
    public function getProviders(): array
    {
        if (!$this->initialized) {
            usort($this->providers, function (ProviderInterface $a, ProviderInterface $b) {
                if ($a->getPriority() == $b->getPriority()) {
                    return 0;
                }

                return $a->getPriority() > $b->getPriority() ? 1 : -1;
            });
            $this->initialized = true;
        }

        return $this->providers;
    }
}
