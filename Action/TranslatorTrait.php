<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Trait TranslatorTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TranslatorTrait
{
    private TranslatorInterface $translator;


    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator
     *
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Translates the given message.
     *
     * @param string      $id
     * @param array       $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string
     *
     * @see TranslatorInterface::trans()
     */
    protected function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
