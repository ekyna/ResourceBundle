<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type\Import;

use Ekyna\Bundle\ResourceBundle\Service\Import\ImportConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

use function array_replace;
use function class_exists;
use function is_array;
use function is_string;
use function Symfony\Component\Translation\t;

/**
 * Class ImportConfigType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ImportConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', Type\FileType::class, [
                'label'       => t('field.file', [], 'EkynaUi'),
                'constraints' => [
                    new NotNull(),
                    new File([
                        'maxSize'          => '1024k',
                        'mimeTypes'        => [
                            'text/plain',
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV file',
                    ]),
                ],
            ])
            ->add('from', Type\IntegerType::class, [
                'label'    => t('import.from', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('to', Type\IntegerType::class, [
                'label'    => t('import.to', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('separator', Type\TextType::class, [
                'label' => t('field.separator', [], 'EkynaUi'),
            ])
            ->add('enclosure', Type\TextType::class, [
                'label' => t('field.separator', [], 'EkynaUi'),
            ]);

        $consumers = $builder->create('consumers', null, [
            'compound' => true,
            'required' => true,
        ]);

        foreach ($options['consumer_types'] as $name => $config) {
            $config = array_replace([
                'type'    => null,
                'options' => [],
            ], $config);

            $consumers->add($name, $config['type'], array_replace($config['options'], [
                'property_path' => "[$name].config",
                'required'      => true,
            ]));
        }

        $builder->add($consumers);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', ImportConfig::class)
            ->setDefault('consumer_types', [])
            ->setAllowedTypes('consumer_types', 'array')
            ->setAllowedValues('consumer_types', function ($value) {
                foreach ($value as $name => $config) {
                    if (!is_string($name)) {
                        throw new InvalidOptionsException('Expected consumer name as string.');
                    }

                    $config = array_replace([
                        'type'    => null,
                        'options' => [],
                    ], $config);

                    if (!(is_string($config['type']) && class_exists($config['type']))) {
                        throw new InvalidOptionsException('Expected consumer config form type class.');
                    }
                    if (!is_array($config['options'])) {
                        throw new InvalidOptionsException('Unexpected consumer config form type options.');
                    }
                }

                return true;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_resource_import_config';
    }
}
