<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AbstractResourceChoiceType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractResourceChoiceType extends AbstractType
{
    public function __construct(protected readonly ResourceHelper $resourceHelper)
    {
    }

    protected function configureClassAndResourceOptions(OptionsResolver $resolver): void
    {
        $class = function (Options $options, $value) {
            if (!empty($value)) {
                return $value;
            }

            if (!isset($options['resource'])) {
                throw new InvalidOptionsException("You must define either 'resource' or 'class' option.");
            }

            return $this
                ->resourceHelper
                ->getResourceConfig($options['resource'])
                ->getEntityClass();
        };

        $resource = function (Options $options, $value) {
            if (!empty($value)) {
                return $value;
            }

            if (!isset($options['class'])) {
                throw new InvalidOptionsException("You must define either 'resource' or 'class' option.");
            }

            return $this
                ->resourceHelper
                ->getResourceConfig($options['class'])
                ->getId();
        };

        // TODO Assert that resource and class points to the same configuration ?

        $resolver
            ->setDefaults([
                'class'    => $class,
                'resource' => $resource,
            ])
            ->setAllowedTypes('class', ['null', 'string'])
            ->setAllowedTypes('resource', ['null', 'string']);
    }

    protected function configureLabelOption(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('label', function (Options $options, $value) {
            if (!empty($value)) {
                return $value;
            }

            $config = $this->resourceHelper->getResourceConfig($options['class']);

            return t($config->getResourceLabel($options['multiple']), [], $config->getTransDomain());
        });
    }

    protected function configurePlaceholderOption(
        OptionsResolver              $resolver,
        TranslatableInterface|string $default
    ): void {
        $resolver->setNormalizer('placeholder', static function (Options $options, $value) use ($default) {
            if (empty($value) && !$options['required'] && !$options['multiple']) {
                return $default;
            }

            return $value;
        });
    }
}
