<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function get_class;
use function is_null;
use function is_object;
use function is_string;

/**
 * Trait ManagerTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait ManagerTrait
{
    private ManagerFactoryInterface $managerFactory;

    /**
     * @required
     */
    public function setManagerFactory(ManagerFactoryInterface $factory): void
    {
        $this->managerFactory = $factory;
    }

    /**
     * Returns the resource manager.
     *
     * @param ResourceInterface|string|null $resourceOrClass
     * @TODO PHP8
     */
    protected function getManager($resourceOrClass = null): ResourceManagerInterface
    {
        if (is_object($resourceOrClass)) {
            $resourceOrClass = get_class($resourceOrClass);
        }

        if (is_null($resourceOrClass)) {
            $resourceOrClass = $this->context->getConfig()->getEntityClass();
        }

        if (!is_string($resourceOrClass)) {
            throw new RuntimeException('No class passed as argument and no context available.');
        }

        return $this->managerFactory->getManager($resourceOrClass);
    }

    /**
     * Persists the resource.
     */
    protected function persist(ResourceInterface $resource): ResourceEventInterface
    {
        return $this->getManager()->save($resource);
    }

    /**
     * Removes (form persistence) the resource.
     */
    protected function remove(ResourceInterface $resource): ResourceEventInterface
    {
        return $this->getManager()->delete($resource);
    }
}
