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
            ->setRequired('class')
            ->setDefault('accessor', 'getChoices')
            ->setDefault('choices', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                $class = $options['class'];
                $method = $options['accessor'];
                $this->validateCallback($class, $method);

                return call_user_func([$class, $method]);
            })
            ->setDefault('constraints', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                $class = $options['class'];
                $method = $options['accessor'];
                $this->validateCallback($class, $method);

                return [
                    new Assert\Choice([
                        'choices'  => call_user_func([$class, $method]),
                        'multiple' => $options['multiple'],
                    ]),
                ];
            })
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('accessor', 'string');
    }

    /**
     * Validates the constant class.
     *
     * @param string $class
     * @param string $method
     *
     * @throws InvalidOptionsException If the class does not exist or if it does not implement the
     *                                 {@link \Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface}
     * @throws InvalidOptionsException If the method does not exist in class, or is not static
     */
    private function validateCallback(string $class, string $method)
    {
        if (!class_exists($class)) {
            throw new InvalidOptionsException(sprintf("The class %s does not exists.", $class));
        }

        if (!is_subclass_of($class, ConstantsInterface::class)) {
            throw new InvalidOptionsException(
                sprintf("The class %s must implements %s", $class, ConstantsInterface::class)
            );
        }

        $rc = new \ReflectionClass($class);
        if (!($rc->hasMethod($method) && ($rm = $rc->getMethod($method)) && $rm->isStatic())) {
            throw new InvalidOptionsException(
                sprintf("Method %s does not exist in class %s, or is not static", $method, $class)
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
