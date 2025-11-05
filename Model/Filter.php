<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Model;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;

use function array_filter;
use function in_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * Class Filter
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Filter
{
    public const EXCLUDE  = 0;
    public const RESTRICT = 1;

    public static function filter(array $choices, int $mode, array $values, bool $filterKey = false): array
    {
        if (Filter::EXCLUDE !== $mode && Filter::RESTRICT !== $mode) {
            throw new InvalidArgumentException('Invalid filter mode');
        }

        if (empty($values)) {
            return Filter::RESTRICT === $mode ? [] : $choices;
        }

        $filter = Filter::EXCLUDE === $mode
            ? static fn($value): bool => !in_array($value, $values, true)
            : static fn($value): bool => in_array($value, $values, true);

        return $filterKey
            ? array_filter($choices, $filter, ARRAY_FILTER_USE_KEY)
            : array_filter($choices, $filter);
    }
}
