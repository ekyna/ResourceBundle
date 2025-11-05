<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Model;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_key_exists;
use function array_keys;
use function reset;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractConstants
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConstants implements ConstantsInterface
{
    public static function getConstants(): array
    {
        return array_keys(static::getConfig());
    }

    public static function getChoices(array $filter = [], int $mode = Filter::EXCLUDE): array
    {
        $choices = [];
        foreach (static::getConfig() as $constant => $config) {
            $choices[$config[0]] = $constant;
        }

        return Filter::filter($choices, $mode, $filter);
    }

    public static function getDefaultChoice(): ?string
    {
        $constants = static::getConstants();

        if ($default = reset($constants)) {
            return $default;
        }

        return null;
    }

    public static function getLabel(string $constant): TranslatableInterface
    {
        static::isValid($constant, true);

        return t(static::getConfig()[$constant][0], [], static::getTranslationDomain());
    }

    public static function getTranslationDomain(): ?string
    {
        return null;
    }

    public static function getTheme(string $constant): ?string
    {
        static::isValid($constant, true);

        $config = static::getConfig()[$constant];

        return $config[1] ?? null;
    }

    public static function isValid(string $constant, bool $throwException = false): bool
    {
        if (array_key_exists($constant, static::getConfig())) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException(sprintf('Unknown constant "%s"', $constant));
        }

        return false;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final protected function __construct()
    {
    }
}
