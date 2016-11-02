<?php

namespace Ekyna\Bundle\ResourceBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AbstractConstantsHelper
 * @package Ekyna\Bundle\ResourceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConstantsHelper
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Renders the state label.
     *
     * @param string $label
     *
     * @return string
     */
    protected function renderLabel($label = 'ekyna_core.value.unknown')
    {
        return $this->translator->trans($label);
    }

    /**
     * Renders the state badge.
     *
     * @param string $label
     * @param string $theme
     *
     * @return string
     */
    protected function renderBadge($label, $theme = 'default')
    {
        return sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }
}
