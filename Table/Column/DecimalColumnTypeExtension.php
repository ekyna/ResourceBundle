<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Table\Column;

use Decimal\Decimal;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\PriceType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Extension\Core\Type\Column\NumberType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class DecimalColumnTypeExtension
 * @package Ekyna\Bundle\ResourceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalColumnTypeExtension extends AbstractColumnTypeExtension
{
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if ($view->vars['value'] instanceof Decimal) {
            $view->vars['value'] = $view->vars['value']->toString();
        }
    }

    public static function getExtendedTypes(): array
    {
        return [NumberType::class, PriceType::class];
    }
}
