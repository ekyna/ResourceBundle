<?php

namespace Ekyna\Bundle\ResourceBundle\Model;

/**
 * Interface ConstantsInterface
 * @package Ekyna\Bundle\CoreBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConstantsInterface
{
    const FILTER_EXCLUDE  = 0;
    const FILTER_RESTRICT = 1;

    /**
     * Returns the constants configuration.
     *
     * Implements this method who must return an array with constants as keys,
     * and configuration arrays as values. The first value of configuration
     * arrays must be the label.<br><br>
     * Example:
     * <code>
     * return array(
     *     self::CONSTANT_1 => array("Constant 1 label", "Constant 1 custom value"),
     *     self::CONSTANT_2 => array("Constant 2 label", "Constant 2 custom value"),
     * );
     * </code>
     * @return array
     */
    public static function getConfig(): array;

    /**
     * Returns the constants.
     *
     * @return array
     */
    public static function getConstants(): array;

    /**
     * Returns the constant choices.
     *
     * @param array $filter The values to filter.
     * @param int   $mode   The filter mode (0: exclusion, 1: restriction).
     *
     * @return array
     */
    public static function getChoices(array $filter = [], int $mode = self::FILTER_EXCLUDE);

    /**
     * Returns the default constant choice.
     *
     * @return string|null
     */
    public static function getDefaultChoice(): ?string;

    /**
     * Returns the label for the given constant.
     *
     * @param string $constant
     *
     * @return string
     */
    public static function getLabel(string $constant): string;

    /**
     * Returns the theme for the given constant.
     *
     * @param string $constant
     *
     * @return string|null
     */
    public static function getTheme(string $constant): ?string;

    /**
     * Returns whether the constant is valid or not.
     *
     * @param string   $constant
     * @param boolean $throwException
     *
     * @return bool
     */
    public static function isValid(string $constant, bool $throwException = false): bool;
}
