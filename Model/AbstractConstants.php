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
    public static function getConstants()
    {
        return array_keys(static::getConfig());
    }

    /**
     * @inheritdoc
     */
    public static function getChoices(array $filter = [])
    {
        $choices = [];
        foreach (static::getConfig() as $constant => $config) {
            if (empty($filter) || !in_array($constant, $filter)) {
                $choices[$config[0]] = $constant;
            }
        }

        return $choices;
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultChoice()
    {
        if ($default = reset(static::getConstants())) {
            return $default;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getLabel($constant)
    {
        if (null === $constant) {
            return 'ekyna_core.value.undefined';
        }

        static::isValid($constant, true);

        return static::getConfig()[$constant][0];
    }

    /**
     * @inheritdoc
     */
    public static function isValid($constant, $throwException = false)
    {
        if (array_key_exists($constant, static::getConfig())) {
            return true;
        }

        if ($throwException) {
            throw new \InvalidArgumentException(sprintf('Unknown constant "%s"', $constant));
        }

        return false;
    }
}
