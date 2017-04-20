<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Model;

/**
 * Interface AclSubjectInterface
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AclSubjectInterface
{
    /**
     * Returns the access control entry subject identifier.
     *
     * @param string $id
     *
     * @return $this|AclSubjectInterface
     */
    public function setAclSubjectId(string $id): AclSubjectInterface;

    /**
     * Returns the access control entry subject identifier.
     *
     * @return string|null
     */
    public function getAclSubjectId(): ?string;

    /**
     * Returns the access control entry parent subject.
     *
     * @return AclSubjectInterface|null
     */
    public function getAclParentSubject(): ?AclSubjectInterface;
}
