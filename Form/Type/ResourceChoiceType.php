<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceChoiceType extends AbstractResourceChoiceType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['disabled']) {
            return;
        }

        if ($options['new_route']) {
            $view->vars['new_route'] = $options['new_route'];
            $view->vars['new_route_params'] = $options['new_route_params'];
        }

        if ($options['list_route']) {
            $view->vars['list_route'] = $options['list_route'];
            $view->vars['list_route_params'] = $options['list_route_params'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'new_route'         => null,
                'new_route_params'  => [],
                'list_route'        => null,
                'list_route_params' => [],
            ])
            ->setAllowedTypes('new_route', ['null', 'string'])
            ->setAllowedTypes('new_route_params', 'array')
            ->setAllowedTypes('list_route', ['null', 'string'])
            ->setAllowedTypes('list_route_params', 'array');

        $this->configureClassAndResourceOptions($resolver);
        $this->configureLabelOption($resolver);
        $this->configurePlaceholderOption($resolver, t('value.none', [], 'EkynaUi'));
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_resource';
    }
}
