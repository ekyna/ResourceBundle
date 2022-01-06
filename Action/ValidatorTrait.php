<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\Validator;

/**
 * Trait ValidatorTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ValidatorTrait
{
    private Validator\Validator\ValidatorInterface $validator;

    /**
     * @required
     */
    public function setValidator(Validator\Validator\ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    protected function getValidator(): Validator\Validator\ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * Validates a value against a constraint or a list of constraints.
     *
     * @param mixed                                       $value       The value to validate
     * @param Validator\Constraint|Validator\Constraint[] $constraints The constraint(s) to validate against
     * @param string|string[]|null                        $groups      The validation groups to validate. If none is given, "Default" is assumed
     *
     * @return Validator\ConstraintViolationListInterface|Validator\ConstraintViolationInterface[]
     *
     * @see Validator\Validator\ValidatorInterface::validate()
     */
    protected function validate($value, $constraints = null, $groups = null): Validator\ConstraintViolationListInterface
    {
        return $this->validator->validate($value, $constraints, $groups);
    }
}
