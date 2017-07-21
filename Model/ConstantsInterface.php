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
    public static function getConfig();

    /**
     * Returns the constants.
     *
     * @return array
     */
    public static function getConstants();

    /**
     * Returns the constant choices.
     *
     * @param array $filter The values to filter.
     * @param int   $mode   The filter mode (0: exclusion, 1: restriction).
     *
     * @return array
     */
    public static function getChoices(array $filter = [], $mode = 0);

    /**
     * Returns the default constant choice.
     *
     * @return mixed
     */
    public static function getDefaultChoice();

    /**
     * Returns the label for the given constant.
     *
     * @param mixed $constant
     *
     * @return string
     */
    public static function getLabel($constant);

    /**
     * Returns whether the constant is valid or not.
     *
     * @param mixed   $constant
     * @param boolean $throwException
     *
     * @return bool
     */
    public static function isValid($constant, $throwException = false);
}
