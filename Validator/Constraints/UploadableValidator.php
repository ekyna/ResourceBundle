<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Validator\Constraints;

use Ekyna\Component\Resource\Model\UploadableInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class UploadableValidator
 * @package Ekyna\Bundle\ResourceBundle\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploadableValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($uploadable, Constraint $constraint)
    {
        if (!$uploadable instanceof UploadableInterface) {
            throw new UnexpectedTypeException($uploadable, UploadableInterface::class);
        }
        if (!$constraint instanceof Uploadable) {
            throw new UnexpectedTypeException($constraint, Uploadable::class);
        }

        /**
         * @var Uploadable          $constraint
         * @var UploadableInterface $uploadable
         */
        if ($uploadable->hasFile() || $uploadable->hasKey()) {
            if (!$uploadable->hasRename()) {
                $this->context
                    ->buildViolation($constraint->nameIsMandatory)
                    ->atPath('rename')
                    ->addViolation();
            }
        } elseif (!$uploadable->hasPath()) {
            $this->context
                ->buildViolation($constraint->fileIsMandatory)
                ->atPath('file')
                ->addViolation();

            if ($uploadable->hasRename()) {
                $this->context
                    ->buildViolation($constraint->leaveBlank)
                    ->atPath('rename')
                    ->addViolation();
            }
        }
    }
}
