<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\DataTransformer;

use Decimal\Decimal;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Throwable;

use function is_float;

/**
 * Class DecimalToStringTransformer
 * @package Ekyna\Bundle\ResourceBundle\Form\DataTransformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalToStringTransformer implements DataTransformerInterface
{
    private ?int $precision;

    public function __construct(?int $precision)
    {
        $this->precision = $precision ?: Decimal::DEFAULT_PRECISION;
    }

    /**
     * @param Decimal|null $value
     *
     * @return string
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Decimal) {
            throw new TransformationFailedException('Expected instance of ' . Decimal::class);
        }

        return $value->toString();
    }

    /**
     * @param string|int $value
     *
     * @return Decimal
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (is_float($value)) {
            $value = (string) $value;
        }

        try {
            $value = new Decimal($value);
        } catch (Throwable $exception) {
            throw new TransformationFailedException(
                'Failed to convert value into instance of ' . Decimal::class, 0, $exception
            );
        }

        return $value->round($this->precision);
    }
}
