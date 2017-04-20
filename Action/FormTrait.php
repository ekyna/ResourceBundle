<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Trait FormTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait FormTrait
{
    private FormFactoryInterface $formFactory;


    /**
     * Sets the form factory.
     *
     * @param FormFactoryInterface $formFactory
     *
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    protected function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @return FormInterface
     */
    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilderInterface
     */
    protected function createFormBuilder($data = null, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(FormType::class, $data, $options);
    }
}
