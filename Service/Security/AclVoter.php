<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function is_object;
use function is_string;

/**
 * Class AclVoter
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AclVoter extends Voter
{
    private AccessDecisionManagerInterface $decision;
    private AclManagerInterface            $acl;
    private ActionRegistryInterface        $actionRegistry;
    private PermissionRegistryInterface    $permissionRegistry;


    public function __construct(
        AccessDecisionManagerInterface $decision,
        AclManagerInterface $acl,
        ActionRegistryInterface $actionRegistry,
        PermissionRegistryInterface $permissionRegistry
    ) {
        $this->decision = $decision;
        $this->acl = $acl;
        $this->actionRegistry = $actionRegistry;
        $this->permissionRegistry = $permissionRegistry;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject): bool
    {
        if (!is_string($attribute) || empty($attribute) || empty($subject)) {
            return false;
        }

        if (is_object($subject) && !$subject instanceof ResourceInterface) {
            return false;
        }

        if (null === $this->acl->getResourceRegistry()->find($subject, false)) {
            return false;
        }

        if ($this->actionRegistry->has($attribute)) {
            return true;
        }

        return $this->permissionRegistry->has($attribute);
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /* TODO ? if ($token instanceof NullToken) {
            // the user is not authenticated, e.g. only allow them to
            // see public posts
        }*/

        // ROLE_SUPER_ADMIN has always access granted
        if ($this->decision->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof AclSubjectInterface) {
            return false;
        }

        if ($this->actionRegistry->has($attribute)) {
            $attribute = $this->actionRegistry->find($attribute)->getPermission() ?: Permission::READ;
        }

        return $this->acl->isGranted($user, $subject, $attribute);
    }
}
