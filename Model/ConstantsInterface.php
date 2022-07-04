<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Model;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface ConstantsInterface
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConstantsInterface
{
    public const FILTER_EXCLUDE  = 0;
    public const FILTER_RESTRICT = 1;

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
     */
    public static function getConfig(): array;

    /**
     * Returns the constants.
     */
    public static function getConstants(): array;

    /**
     * Returns the constant choices.
     *
     * @param array $filter The values to filter.
     * @param int   $mode   The filter mode (0: exclusion, 1: restriction).
     */
    public static function getChoices(array $filter = [], int $mode = self::FILTER_EXCLUDE): array;

    /**
     * Returns the default constant choice.
     */
    public static function getDefaultChoice(): ?string;

    /**
     * Returns the label for the given constant.
     */
    public static function getLabel(string $constant): TranslatableInterface;

    /**
     * Returns the translation domain for both label and choices.
     */
    public static function getTranslationDomain(): ?string;

    /**
     * Returns the theme for the given constant.
     */
    public static function getTheme(string $constant): ?string;

    /**
     * Returns whether the constant is valid or not.
     */
    public static function isValid(string $constant, bool $throwException = false): bool;
}
