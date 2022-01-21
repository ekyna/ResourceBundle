<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\ConstantChoiceTypeHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

use function call_user_func;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\CoreBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $helper = new ConstantChoiceTypeHelper($this->translator);

        $helper->configureOptions($resolver);

        $resolver
            ->setDefault('constraints', function (Options $options, $value) use ($helper) {
                if (!empty($value)) {
                    return $value;
                }

                $class = $options['class'];
                $method = $options['accessor'];

                $helper->validateCallback($class, $method);

                return [
                    new Assert\Choice([
                        'choices'  => call_user_func([$class, $method]),
                        'multiple' => $options['multiple'],
                    ]),
                ];
            });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
