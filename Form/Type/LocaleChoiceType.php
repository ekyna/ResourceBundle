<?php

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocaleChoiceType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LocaleChoiceType extends LocaleType
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
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('label', 'ekyna_core.field.locale')
            ->setNormalizer('placeholder', function (Options $options, $value) {
                if (empty($value) && !$options['required'] && !$options['multiple']) {
                    $value = 'ekyna_core.value.none';
                }

                return $value;
            });
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
}
