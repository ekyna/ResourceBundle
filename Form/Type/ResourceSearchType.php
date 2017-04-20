<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use LogicException;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\SerializerInterface;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class ResourceSearchType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceSearchType extends AbstractType
{
    private ResourceHelper             $helper;
    private RepositoryFactoryInterface $factory;
    private SerializerInterface        $serializer;


    public function __construct(
        ResourceHelper $helper,
        RepositoryFactoryInterface $factory,
        SerializerInterface $serializer
    ) {
        $this->helper = $helper;
        $this->factory = $factory;
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $repository = $this->factory->getRepository($options['class']);

        $builder->addViewTransformer(
            new ResourceToIdentifierTransformer($repository, $options['identifier'], $options['multiple'])
        );

        if ($options['multiple']) {
            $builder
                ->addViewTransformer(new CollectionToArrayTransformer(), true)
                ->addEventSubscriber(new MergeDoctrineCollectionListener());
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $repository = $this->factory->getRepository($options['class']);

        $identifier = $options['identifier'];

        $transformer = new ResourceToIdentifierTransformer($repository, $identifier, $options['multiple']);
        $value = $transformer->transform($data = $form->getData());

        $choices = [];
        if (!empty($data)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $createChoice = function ($entity) use ($accessor, $identifier) {
                return new ChoiceView($entity, (string)$accessor->getValue($entity, $identifier), (string)$entity, [
                    'data-entity' => $this->serializer->normalize($entity, 'json', ['groups' => ['Search']]),
                ]);
            };
            if ($options['multiple']) {
                foreach ($data as $entity) {
                    $choices[] = $createChoice($entity);
                }
            } else {
                $choices[] = $createChoice($data);
            }
        }

        // Search Url
        if (!empty($options['search_route'])) {
            $route = $options['search_route'];
        } elseif (!empty($options['search_action'])) {
            $route = $this->helper->getRoute($options['resource'], $options['search_action']);
        } else {
            throw new LogicException('Failed to find search route name.');
        }

        $view->vars = array_replace($view->vars, [
            'value'             => $value,
            'choices'           => $choices,
            'preferred_choices' => [],
            'placeholder'       => $options['placeholder'],
            'multiple'          => $options['multiple'],
            'expanded'          => false,
        ]);

        if ($options['multiple']) {
            // Add "[]" to the name in case a select tag with multiple options is
            // displayed. Otherwise only one of the selected options is sent in the
            // POST request.
            $view->vars['full_name'] .= '[]';
        }

        $view->vars['attr']['data-search'] = $this
            ->helper
            ->getUrlGenerator()
            ->generate($route, $options['search_parameters']); // TODO Locale parameter ?

        // Select2 options
        $view->vars['attr']['data-allow-clear'] = (!$options['required'] && !$options['multiple']) ? 1 : 0;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // TODO Assert that resource and class points to the same configuration.

        $class = function (Options $options, $value) {
            if (empty($value)) {
                if (!isset($options['resource'])) {
                    throw new InvalidOptionsException("You must define either 'resource' or 'class' option.");
                }

                return $this
                    ->helper
                    ->getResourceConfig($options['resource'])
                    ->getEntityClass();
            }

            return $value;
        };

        $resource = function (Options $options, $value) {
            if (empty($value)) {
                if (!isset($options['class'])) {
                    throw new InvalidOptionsException("You must define either 'resource' or 'class' option.");
                }

                return $this
                    ->helper
                    ->getResourceConfig($options['class'])
                    ->getId();
            }

            return $value;
        };

        $emptyData = function (Options $options) {
            if ($options['multiple']) {
                return [];
            }

            return '';
        };

        $placeholderNormalizer = function (Options $options, $placeholder) {
            if ($options['multiple']) {
                // never use an empty value for this case
                return null;
            }

            // empty value has been set explicitly
            return $placeholder;
        };

        $resolver
            ->setDefaults([
                'class'             => $class,
                'resource'          => $resource,
                'identifier'        => 'id',
                'search_action'     => null,
                'search_route'      => null,
                'search_parameters' => [],
                'compound'          => false,
                'multiple'          => false,
                'empty_data' => $emptyData,
                'placeholder'       => $placeholderNormalizer,
                'select2'           => false,
            ])
            ->setAllowedTypes('identifier', 'string')
            ->setAllowedTypes('search_action', ['null', 'string'])
            ->setAllowedTypes('search_route', ['null', 'string'])
            ->setAllowedTypes('search_parameters', 'array')
            ->setAllowedTypes('multiple', 'bool')
            ->setNormalizer('label', function (Options $options, $value) {
                if (empty($value)) {
                    $config = $this->helper->getResourceConfig($options['class']);

                    return t($config->getResourceLabel($options['multiple']), [], $config->getTransDomain());
                }

                return $value;
            })
            ->setNormalizer('placeholder', function (Options $options, $value) {
                if (empty($value) && !$options['required'] && !$options['multiple']) {
                    return t('field.search', [], 'EkynaUi');
                }

                return $value;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_resource_search';
    }
}
