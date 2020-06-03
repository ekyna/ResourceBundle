<?php

namespace Ekyna\Bundle\ResourceBundle\Model;

/**
 * Class AbstractConstants
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConstants implements ConstantsInterface
{
    /**
     * @inheritdoc
     */
    public static function getConstants(): array
    {
        return array_keys(static::getConfig());
    }

    /**
     * @inheritdoc
     */
    public static function getChoices(array $filter = [], $mode = self::FILTER_EXCLUDE)
    {
        if (0 !== $mode && 1 !== $mode) {
            throw new \InvalidArgumentException('Invalid filter mode');
        }

        if (!empty($filter)) {
            foreach ($filter as $value) {
                static::isValid($value, true);
            }
        }

        $choices = [];
        foreach (static::getConfig() as $constant => $config) {
            if (!empty($filter)) {
                // Exclusion
                if (($mode === 0) && in_array($constant, $filter, true)) {
                    continue;
                }
                // Restriction
                if (($mode === 1) && !in_array($constant, $filter, true)) {
                    continue;
                }
            }

            $choices[$config[0]] = $constant;
        }

        return $choices;
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultChoice(): ?string
    {
        $constants = static::getConstants();

        if ($default = reset($constants)) {
            return $default;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getLabel(string $constant): string
    {
        if (null === $constant) {
            return 'ekyna_core.value.undefined';
        }

        static::isValid($constant, true);

        return static::getConfig()[$constant][0];
    }

    /**
     * Returns the constant's theme.
     *
     * @param string $constant
     *
     * @return string
     */
    public static function getTheme(string $constant): ?string
    {
        static::isValid($constant, true);

        $config = static::getConfig()[$constant];

        return $config[1] ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function isValid(string $constant, bool $throwException = false): bool
    {
        if (array_key_exists($constant, static::getConfig())) {
            return true;
        }

        if ($throwException) {
            throw new \InvalidArgumentException(sprintf('Unknown constant "%s"', $constant));
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
