<?php

namespace Ekyna\Bundle\ResourceBundle\Behat\Context;

use Ekyna\Behat\Context\BaseContext;

/**
 * Class ResourceContext
 * @package Ekyna\Bundle\ResourceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceContext extends BaseContext
{
    /**
     * Resource saved confirmation message
     *
     * @Then /^(?:|I )should see the resource saved confirmation message$/
     */
    public function resourceSavedConfirmationMessage()
    {
        $this->assertPageContainsText('La ressource a été sauvegardée avec succès');
    }

    /**
     * Resource removed confirmation message
     *
     * @Then /^(?:|I )should see the resource removed confirmation message$/
     */
    public function resourceRemovedConfirmationMessage()
    {
        $this->assertPageContainsText('La ressource a été supprimée avec succès');
    }
}
