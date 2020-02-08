<?php

namespace Ekyna\Bundle\ResourceBundle\Table\Column;

use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\ResourceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractColumnType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $label = $this->translator->trans(
            call_user_func($options['class'] . '::getLabel', $view->vars['value'])
        );

        if (!$options['theme']) {
            $view->vars['value'] = $label;

            return;
        }

        $theme = call_user_func($options['class'] . '::getTheme', $view->vars['value']);

        $view->vars['value'] = sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'label' => 'ekyna_core.field.status',
                'theme' => false,
            ])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('theme', 'bool')
            ->setAllowedValues('class', function ($class) {
                return is_subclass_of($class, ConstantsInterface::class);
            });
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return PropertyType::class;
    }
}
