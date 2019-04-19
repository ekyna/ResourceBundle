<?php

namespace Ekyna\Bundle\ResourceBundle\Form\ChoiceList;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Intl\Intl;

/**
 * Class LocaleChoiceLoader
 * @package Ekyna\Bundle\ResourceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LocaleChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var array
     */
    private $locales;

    /**
     * @var ArrayChoiceList
     */
    private $choiceList;


    /**
     * Constructor.
     *
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritDoc
     */
    public function loadChoiceList($value = null)
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $locales = [];
        foreach ($this->locales as $locale) {
            $name = Intl::getLocaleBundle()->getLocaleName($locale);
            $locales[mb_convert_case($name, MB_CASE_TITLE)] = $locale;
        }

        return $this->choiceList = new ArrayChoiceList($locales, $value);
    }

    /**
     * @inheritDoc
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @inheritDoc
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
