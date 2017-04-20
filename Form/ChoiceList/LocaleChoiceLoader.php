<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\ChoiceList;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Intl\Locales;

use function array_filter;
use function mb_convert_case;

/**
 * Class LocaleChoiceLoader
 * @package Ekyna\Bundle\ResourceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LocaleChoiceLoader implements ChoiceLoaderInterface
{
    private array            $locales;
    private ?ArrayChoiceList $choiceList = null;


    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function loadChoiceList(callable $value = null): ChoiceListInterface
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $locales = [];
        foreach ($this->locales as $locale) {
            $name = Locales::getName($locale);
            $locales[mb_convert_case($name, MB_CASE_TITLE)] = $locale;
        }

        return $this->choiceList = new ArrayChoiceList($locales, $value);
    }

    /**
     * @inheritDoc
     */
    public function loadChoicesForValues(array $values, $value = null): array
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
    public function loadValuesForChoices(array $choices, $value = null): array
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
