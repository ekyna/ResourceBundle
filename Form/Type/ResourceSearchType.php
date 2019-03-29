<?php

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ResourceSearchType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceSearchType extends AbstractType
{
    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $configurationRegistry
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        ConfigurationRegistry $configurationRegistry,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $configuration = $this->configurationRegistry->findConfiguration($options['class']);

        // Label
        $view->vars['label'] = $options['label'] === null
            ? $configuration->getResourceLabel($options['multiple'])
            : $options['label'];

        // Search Url
        $route = $options['search_route'] ?: $configuration->getRoute('search');
        $parameters = $options['search_route_params'] ?: []; // TODO locale
        $view->vars['attr']['data-search'] = $this->urlGenerator->generate($route, $parameters);

        // Select2 search Options
        $allowClear = $options['required'] ? 0 : 1;
        if (null !== $options['allow_clear']) {
            $allowClear = $options['allow_clear'] ? 1 : 0;
        }
        $view->vars['attr']['data-allow-clear'] = $allowClear;
        $view->vars['attr']['data-format'] = $options['format_function']
            ?: "if(!data.id)return 'Rechercher'; return $('<span>'+data.text+'</span>');";

        // CSS class (to trigger JS form plugin)
        $classes = isset($view->vars['attr']['class']) ?
            explode(' ', $view->vars['attr']['class'])
            : [];
        if (!in_array('resource-search', $classes)) {
            $classes[] = 'resource-search';
        }
        $view->vars['attr']['class'] = implode(' ', $classes);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'search_route'        => null,
                'search_route_params' => null,
                'format_function'     => null,
                'allow_clear'         => null,
                'select2'             => false,
                'choice_attr' => function($val, $key, $index) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['class' => 'attending_'.strtolower($key)];
                },
            ])
            ->setAllowedTypes('search_route', ['null', 'string'])
            ->setAllowedTypes('search_route_params', ['null', 'array'])
            ->setAllowedTypes('format_function', ['null', 'string'])
            ->setAllowedTypes('allow_clear', ['null', 'bool']);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
