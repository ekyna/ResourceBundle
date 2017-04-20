<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\ChoiceList\LocaleChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class LocaleChoiceType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LocaleChoiceType extends AbstractType
{
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'label'                     => t('field.locale', [], 'EkynaUi'),
                'locales'                   => $this->locales,
                'choice_loader'             => function (Options $options) {
                    return new LocaleChoiceLoader($options['locales']);
                },
                'choice_translation_domain' => false,
            ])
            ->setAllowedTypes('locales', ['array', 'null'])
            ->setNormalizer('placeholder', function (Options $options, $value) {
                if (empty($value) && !$options['required'] && !$options['multiple']) {
                    $value = 'value.none';
                }

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'locale';
    }
}
