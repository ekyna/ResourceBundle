<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing\Loader;

use function array_key_exists;

/**
 * Trait HostsTrait
 * @package Ekyna\Bundle\ResourceBundle\Service\Routing\Loader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait HostsTrait
{
    private array $hosts;

    public function setHosts(array $hosts): void
    {
        $this->hosts = $hosts;
    }

    /**
     * Configures i18n hosts if needed.
     */
    protected function addHosts(array &$config): void
    {
        if (!$this->shouldAddHosts($config)) {
            return;
        }

        $config['host'] = $this->hosts;
    }

    /**
     * Returns whether i18n hosts should be added to the given route configuration.
     */
    private function shouldAddHosts(array $config): bool
    {
        if (isset($config['host'])) {
            return false;
        }

        if (isset($config['options']) && array_key_exists('ekyna_i18n', $config['options'])) {
            return true;
        }

        return false;
    }
}
