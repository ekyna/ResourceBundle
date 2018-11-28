<?php

namespace Ekyna\Bundle\ResourceBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface as BaseUser;

/**
 * Interface UserInterface
 * @package Ekyna\Bundle\ResourceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UserInterface extends BaseUser
{
    /**
     * Returns the security identifier.
     *
     * @return string
     */
    public function getSecurityId();
}
