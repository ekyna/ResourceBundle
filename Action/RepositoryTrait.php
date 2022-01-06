<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

use function is_null;

/**
 * Trait RepositoryTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait RepositoryTrait
{
    private RepositoryFactoryInterface $repositoryFactory;

    /**
     * @required
     */
    public function setRepositoryFactory(RepositoryFactoryInterface $factory): void
    {
        $this->repositoryFactory = $factory;
    }

    /**
     * Returns the repository for the given resource class.
     */
    protected function getRepository(string $class = null): ResourceRepositoryInterface
    {
        if (is_null($class)) {
            $class = $this->context->getConfig()->getEntityClass();
        }

        return $this->repositoryFactory->getRepository($class);
    }
}
