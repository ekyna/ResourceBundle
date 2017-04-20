<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Helper;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AbstractConstantsHelper
 * @package Ekyna\Bundle\ResourceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConstantsHelper
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Renders the constant label.
     */
    protected function renderLabel(?TranslatableInterface $label): string
    {
        if (!$label) {
            $label = t('value.unknown', [], 'EkynaUi');
        }

        return $label->trans($this->translator);
    }

    /**
     * Renders the constant badge.
     */
    protected function renderBadge(string $label, string $theme = 'default'): string
    {
        return sprintf('<span class="label label-%s product-type-badge">%s</span>', $theme, $label);
    }
}
