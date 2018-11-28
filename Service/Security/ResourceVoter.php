<?php

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Ekyna\Bundle\ResourceBundle\Model\UserInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class ResourceVoter
 * @package Ekyna\Bundle\ResourceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceVoter extends Voter
{
    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry          $registry
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(
        ConfigurationRegistry $registry,
        AccessDecisionManagerInterface $decisionManager
    ) {
        $this->registry = $registry;
        $this->decisionManager = $decisionManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        if (is_string($subject) && $this->registry->findConfiguration($subject)) {
            return true;
        }

        if ($subject instanceof ResourceInterface) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // ROLE_SUPER_ADMIN has always access granted
        if ($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // TODO
        //$id = $user->getSecurityId();

        return true;
    }
}
