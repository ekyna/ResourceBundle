<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Uploadable
 * @package Ekyna\Bundle\ResourceBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Uploadable extends Constraint
{
    public string $fileIsMandatory = 'uploadable.file_is_mandatory';
    public string $nameIsMandatory = 'uploadable.name_is_mandatory';
    public string $leaveBlank      = 'uploadable.leave_blank';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
