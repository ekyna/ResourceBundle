<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Extension;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\DecimalToStringTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DecimalTypeExtension
 * @package Ekyna\Bundle\ResourceBundle\Form\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['decimal']) {
            return;
        }

        $builder->addModelTransformer(new DecimalToStringTransformer($options['scale'] ?? null));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'decimal'    => false,
                'empty_data' => function (Options $options) {
                    return $options['required'] && $options['decimal'] ? '0' : null;
                },
            ])
            ->setAllowedTypes('decimal', 'bool');
    }

    public static function getExtendedTypes(): iterable
    {
        return [IntegerType::class, NumberType::class, MoneyType::class];
    }
}
