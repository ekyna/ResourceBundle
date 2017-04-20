<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Model;

/**
 * Trait AclSubjectTrait
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AclSubjectTrait
{
    private ?string $aclSubjectId = null;


    /**
     * Returns the access control list subject identifier.
     *
     * @return string|null
     */
    public function getAclSubjectId(): ?string
    {
        return $this->aclSubjectId;
    }

    /**
     * Sets the access control list subject identifier.
     *
     * @param string $id
     *
     * @return $this|AclSubjectInterface
     */
    public function setAclSubjectId(string $id): AclSubjectInterface
    {
        $this->aclSubjectId = $id;

        return $this;
    }

    /**
     * Returns the access control list parent subject.
     *
     * @return AclSubjectInterface|null
     */
    public function getAclParentSubject(): ?AclSubjectInterface
    {
        return null;
    }
}
