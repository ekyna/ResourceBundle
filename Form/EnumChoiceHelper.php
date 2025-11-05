<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form;

use Ekyna\Bundle\ResourceBundle\Model\Filter;
use Ekyna\Component\Resource\Enum\LabelInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_combine;
use function array_map;
use function array_values;
use function is_array;

/**
 * Class EnumChoiceHelper
 * @package Ekyna\Bundle\ResourceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EnumChoiceHelper
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('class')
            ->setAllowedTypes('class', 'string')
            ->setAllowedValues('class', enum_exists(...))
            ->setDefaults([
                'filter'      => [],
                'filter_mode' => Filter::EXCLUDE,
            ])
            ->setDefault('choices', function (Options $options) {
                $class = $options['class'];

                $choices = $class::cases();

                $choices = Filter::filter($choices, $options['filter_mode'], $options['filter']);

                if (!is_a($class, LabelInterface::class, true)) {
                    return $choices;
                }

                $labels = array_map(function (LabelInterface $label) {
                    return $label->label()->trans($this->translator);
                }, array_values($choices));

                return array_combine($labels, array_values($choices));
            })
            ->setDefault('choice_translation_domain', false)
            ->setDefault('placeholder', function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                if (isset($options['required']) && $options['required']) {
                    return $value;
                }

                return $this->translator->trans('value.none', [], 'EkynaUi');
            })
            ->setAllowedTypes('filter', ['string', 'string[]'])
            ->setAllowedValues('filter_mode', [
                Filter::EXCLUDE,
                Filter::RESTRICT,
            ])
            ->setNormalizer('filter', function (Options $options, $value) {
                if (!is_array($value)) {
                    return (array)$value;
                }

                return $value;
            });
    }
}
