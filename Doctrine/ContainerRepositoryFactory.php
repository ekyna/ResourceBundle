<?php

namespace Ekyna\Bundle\ResourceBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ContainerRepositoryFactory as Wrapped;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ObjectRepository;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerRepositoryFactory
 * @package Ekyna\Bundle\ResourceBundle\Doctrine
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContainerRepositoryFactory implements RepositoryFactory
{
    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var ObjectRepository[]
     */
    private $repositories;

    /**
     * @var Wrapped
     */
    private $wrapped;


    /**
     * Constructor.
     *
     * @param ContainerInterface    $container The service locator containing the repositories
     * @param ConfigurationRegistry $registry  The resource configuration registry
     */
    public function __construct(ContainerInterface $container, ConfigurationRegistry $registry)
    {
        $this->container = $container;
        $this->registry  = $registry;
        $this->repositories = [];

        $this->wrapped   = new Wrapped($container);
    }

    /**
     * @inheritDoc
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        if (!$configuration = $this->registry->findConfiguration($entityName, false)) {
            return $this->wrapped->getRepository($entityManager, $entityName);
        }

        return $this->repositories[$entityName] =
            $this->container->get($configuration->getServiceKey('repository'));
    }
}
