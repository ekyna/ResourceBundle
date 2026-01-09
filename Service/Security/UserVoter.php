<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class UserVoter
 * @package Ekyna\Bundle\ResourceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Create a custom access decision manager that:
 *       - filters voters whether they apply to a given user class.
 *       - caches the voters regarding the User class they support.
 * @see \Symfony\Component\Security\Core\Authorization\AccessDecisionManager
 */
abstract class UserVoter extends Voter
{
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$token->getUser() instanceof ($this->getUserClass())) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        return parent::vote($token, $subject, $attributes);
    }

    abstract protected function getUserClass(): string;
}
