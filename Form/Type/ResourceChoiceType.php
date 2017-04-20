<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceChoiceType extends AbstractType
{
    private ResourceHelper      $helper;
    private TranslatorInterface $translator;


    public function __construct(ResourceHelper $helper, TranslatorInterface $translator)
    {
        $this->helper = $helper;
        $this->translator = $translator;
    }

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
        // TODO Assert that resource and class points to the same configuration.

        $resolver
            ->setDefaults([
                'class'              => function (Options $options, $value) {
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
                },
                'resource'           => function (Options $options, $value) {
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
                },
                'new_route'          => null,
                'new_route_params'   => [],
                'list_route'         => null,
                'list_route_params'  => [],
            ])
            ->setAllowedTypes('resource', ['null', 'string'])
            ->setAllowedTypes('new_route', ['null', 'string'])
            ->setAllowedTypes('new_route_params', 'array')
            ->setAllowedTypes('list_route', ['null', 'string'])
            ->setAllowedTypes('list_route_params', 'array')
            ->setNormalizer('label', function (Options $options, $value) {
                if (empty($value)) {
                    $config = $this->helper->getResourceConfig($options['class']);

                    return t($config->getResourceLabel($options['multiple']), [], $config->getTransDomain());
                }

                return $value;
            })
            ->setNormalizer('placeholder', function (Options $options, $value) {
                if (empty($value) && !$options['required'] && !$options['multiple']) {
                    return $this
                        ->translator
                        ->trans('value.none', [], 'EkynaUi');
                }

                return $value;
            });
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
