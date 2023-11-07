<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form;

use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use ReflectionClass;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Contracts\Translation\TranslatorInterface;

use function array_combine;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function call_user_func;
use function class_exists;
use function is_array;
use function is_subclass_of;
use function sprintf;

/**
 * Class ConstantChoiceTypeHelper
 * @package Ekyna\Bundle\ResourceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceTypeHelper
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'accessor'    => 'getChoices',
                'filter'      => [],
                'filter_mode' => ConstantsInterface::FILTER_EXCLUDE,
            ])
            ->setDefault('choices', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                $class  = $options['class'];
                $method = $options['accessor'];
                $this->validateCallback($class, $method);

                $parameters = [];
                if ($method === 'getChoices') {
                    $parameters[] = $options['filter'];
                    $parameters[] = $options['filter_mode'];
                }

                /** @see ConstantsInterface::getChoices() */
                $choices = call_user_func([$class, $method], ...$parameters);

                /** @see ConstantsInterface::getTranslationDomain() */
                $domain = call_user_func($options['class'] . '::getTranslationDomain');

                return array_combine(array_map(function(string $label) use ($domain) {
                    return $this->translator->trans($label, [], $domain);
                }, array_keys($choices)), array_values($choices));
            })
            ->setDefault('choice_translation_domain', false)
            ->setDefault('placeholder', function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                if (isset($options['required']) && $options['required']) {
                    return $value;
                }

                return $this->translator->trans('value.none', [], 'EkynaUi');
            })
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('accessor', 'string')
            ->setAllowedTypes('filter', ['string', 'string[]'])
            ->setAllowedValues('class', function($value) {
                return is_subclass_of($value, ConstantsInterface::class);
            })
            ->setAllowedValues('filter_mode', [
                ConstantsInterface::FILTER_EXCLUDE,
                ConstantsInterface::FILTER_RESTRICT,
            ])
            ->setNormalizer('filter', function(Options $options, $value) {
                if (!is_array($value)) {
                    return (array)$value;
                }

                return $value;
            });
    }

    /**
     * Validates the constant class.
     *
     * @throws InvalidOptionsException If the class does not exist or if it does not implement the
     *                                 {@link \Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface}
     * @throws InvalidOptionsException If the method does not exist in class, or is not static
     */
    public function validateCallback(string $class, string $method): void
    {
        if (!class_exists($class)) {
            throw new InvalidOptionsException(sprintf('The class %s does not exists.', $class));
        }

        if (!is_subclass_of($class, ConstantsInterface::class)) {
            throw new InvalidOptionsException(
                sprintf('The class %s must implements %s', $class, ConstantsInterface::class)
            );
        }

        $rc = new ReflectionClass($class);
        if (!($rc->hasMethod($method) && ($rm = $rc->getMethod($method)) && $rm->isStatic())) {
            throw new InvalidOptionsException(
                sprintf('Method %s does not exist in class %s, or is not static', $method, $class)
            );
        }
    }
}
