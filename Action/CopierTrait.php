<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Copier\CopierInterface;

/**
 * Trait CopierTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait CopierTrait
{
    private CopierInterface $copier;

    /**
     * @required
     */
    public function setCopier(CopierInterface $copier): void
    {
        $this->copier = $copier;
    }

    protected function getCopier(): CopierInterface
    {
        return $this->copier;
    }
}
