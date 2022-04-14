<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type\Import;

use Ekyna\Bundle\UiBundle\Form\Type\KeyValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

use function call_user_func;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractConfigType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataClass = $options['data_class'];

        $builder
            ->add('numbers', KeyValueCollectionType::class, [
                'label'         => t('field.columns', [], 'EkynaUi'),
                'key_options'   => [
                    'label'        => t('field.field', [], 'EkynaUi'),
                    'choice_label' => function ($choice, $key) use ($dataClass) {
                        return call_user_func($dataClass . '::getLabel', $key);
                    },
                ],
                'value_type'    => IntegerType::class,
                'value_options' => [
                    'label' => t('field.number', [], 'EkynaUi'),
                ],
                'allowed_keys'  => call_user_func($dataClass . '::getKeys'),
            ]);
    }
}
