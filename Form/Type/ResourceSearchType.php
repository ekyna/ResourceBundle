<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use LogicException;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
class ResourceSearchType extends AbstractResourceChoiceType
{
    public function __construct(
        ResourceHelper                              $resourceHelper,
        private readonly RepositoryFactoryInterface $factory,
        private readonly SerializerInterface        $serializer
    ) {
        parent::__construct($resourceHelper);
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
            $route = $this->resourceHelper->getRoute($options['resource'], $options['search_action']);
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
            // displayed. Otherwise, only one of the selected options is sent in the
            // POST request.
            $view->vars['full_name'] .= '[]';
        }

        $view->vars['attr']['data-search'] = $this
            ->resourceHelper
            ->getUrlGenerator()
            ->generate($route, $options['search_parameters']); // TODO Locale parameter ?

        // Select2 options
        $view->vars['attr']['data-allow-clear'] = (!$options['required'] && !$options['multiple']) ? 1 : 0;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $emptyData = static function (Options $options) {
            if ($options['multiple']) {
                return [];
            }

            return '';
        };

        $placeholder = static function (Options $options, $placeholder) {
            if ($options['multiple']) {
                // never use an empty value for this case
                return null;
            }

            // empty value has been set explicitly
            return $placeholder;
        };

        $resolver
            ->setDefaults([
                'identifier'        => 'id',
                'search_action'     => null,
                'search_route'      => null,
                'search_parameters' => [],
                'compound'          => false,
                'multiple'          => false,
                'empty_data'        => $emptyData,
                'placeholder'       => $placeholder,
                'select2'           => false,
            ])
            ->setAllowedTypes('identifier', 'string')
            ->setAllowedTypes('search_action', ['null', 'string'])
            ->setAllowedTypes('search_route', ['null', 'string'])
            ->setAllowedTypes('search_parameters', 'array')
            ->setAllowedTypes('multiple', 'bool');

        $this->configureClassAndResourceOptions($resolver);
        $this->configureLabelOption($resolver);
        $this->configurePlaceholderOption($resolver, t('field.search', [], 'EkynaUi'));
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_resource_search';
    }
}
