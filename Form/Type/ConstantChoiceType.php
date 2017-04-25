<?php

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\CoreBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('class', null)
            ->setDefault('choices', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                $this->validateConstantClass($class = $options['class']);

                return call_user_func([$class, 'getChoices']);
            })
            ->setDefault('constraints', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                $this->validateConstantClass($class = $options['class']);

                return [
                    new Assert\Choice([
                        'choices'  => call_user_func([$class, 'getConstants']),
                        'multiple' => $options['multiple'],
                    ]),
                ];
            })
            ->setAllowedTypes('class', 'string');
    }

    /**
     * Validates the constant class.
     *
     * @param string $class
     *
     * @throws InvalidOptionsException If the class does not exist or if it does not implement the
     *                                 {@link \Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface}
     */
    private function validateConstantClass($class)
    {
        if (!class_exists($class)) {
            throw new InvalidOptionsException(sprintf("The class %s does not exists.", $class));
        }

        if (!is_subclass_of($class, ConstantsInterface::class)) {
            throw new InvalidOptionsException(
                sprintf("The class %s must implements %s", $class, ConstantsInterface::class)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
