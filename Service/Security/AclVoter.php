<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function get_class;
use function is_string;

/**
 * Class AclVoter
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AclVoter extends Voter
{
    public function __construct(
        private readonly AccessDecisionManagerInterface $decisionManager,
        private readonly AclManagerInterface            $aclManager,
        private readonly ActionRegistryInterface        $actionRegistry,
        private readonly PermissionRegistryInterface    $permissionRegistry,
        private readonly ResourceRegistryInterface      $resourceRegistry,
    ) {
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (empty($attribute) || empty($subject)) {
            return false;
        }

        if ($subject instanceof ResourceInterface) {
            $subject = get_class($subject);
        }

        if (!is_string($subject)) {
            return false;
        }

        if (null === $this->resourceRegistry->has($subject)) {
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
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /* TODO ? if ($token instanceof NullToken) {
            // the user is not authenticated, e.g. only allow them to
            // see public resources
        }*/

        // ROLE_SUPER_ADMIN has always access granted
        if ($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof AclSubjectInterface) {
            return false;
        }

        if (!$this->actionRegistry->has($attribute)) {
            return $this->aclManager->isGranted($user, $subject, $attribute);
        }

        $aConfig = $this->actionRegistry->find($attribute);
        $attributes = $aConfig->getPermissions();

        $attributes += $this
                           ->resourceRegistry
                           ->find($subject)
                           ->getAction($aConfig->getName())['permissions'] ?? [];

        if (empty($attributes)) {
            return $this->aclManager->isGranted($user, $subject, Permission::READ);
        }

        foreach ($attributes as $attribute) {
            if (!$this->aclManager->isGranted($user, $subject, $attribute)) {
                return false;
            }
        }

        return true;
    }
}
