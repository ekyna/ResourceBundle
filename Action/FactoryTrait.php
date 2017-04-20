<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function is_null;

/**
 * Trait FactoryTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait FactoryTrait
{
    private FactoryFactoryInterface $factoryFactory;


    /**
     * Sets the factory factory.
     *
     * @param FactoryFactoryInterface $factory
     *
     * @required
     */
    public function setFactoryFactory(FactoryFactoryInterface $factory): void
    {
        $this->factoryFactory = $factory;
    }

    /**
     * Returns the resource factory.
     *
     * @param string|null $class
     *
     * @return ResourceFactoryInterface
     */
    protected function getFactory(string $class = null): ResourceFactoryInterface
    {
        if (is_null($class)) {
            $context = $this->{'context'};

            if ($context instanceof Context) {
                $class = $context->getConfig()->getEntityClass();
            }
        }

        if (is_null($class)) {
            throw new RuntimeException('No class passed as argument and no context available.');
        }

        return $this->factoryFactory->getFactory($class);
    }

    /**
     * Creates a new resource.
     *
     * @return ResourceInterface
     */
    protected function createResource(): ResourceInterface
    {
        return $this->getFactory()->createFromContext($this->context);
    }
}
