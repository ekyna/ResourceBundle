<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;

use function uniqid;

/**
 * Class AclIdGenerator
 * @package Ekyna\Bundle\ResourceBundle\Service\Security
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AclIdGenerator
{
    /**
     * Generates the ACL subject identifier.
     *
     * @param AclSubjectInterface $subject
     *
     * @return bool Whether the identifier has been generated.
     */
    public function generate(AclSubjectInterface $subject): bool
    {
        if (!empty($subject->getAclSubjectId())) {
            return false;
        }

        $subject->setAclSubjectId(uniqid('', true));

        return true;
    }
}
