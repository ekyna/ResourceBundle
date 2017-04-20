<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Uploader;

/**
 * Class UploadToggler
 * @package Ekyna\Bundle\ResourceBundle\Service\Uploader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploadToggler
{
    private bool $enabled = true;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
