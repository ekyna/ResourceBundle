<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Command;

use Ekyna\Bundle\ResourceBundle\Behavior\AceSubjectBehavior;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclIdGenerator;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AclGenerateSubjectIdCommand
 * @package Ekyna\Bundle\ResourceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AclGenerateSubjectIdCommand extends Command
{
    protected static $defaultName = 'ekyna:resource:acl:generate-subject-id';

    private ResourceRegistryInterface  $resourceRegistry;
    private RepositoryFactoryInterface $repositoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private AclIdGenerator             $generator;


    public function __construct(
        ResourceRegistryInterface $resourceRegistry,
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface $managerFactory,
        AclIdGenerator $generator
    ) {
        parent::__construct();

        $this->resourceRegistry = $resourceRegistry;
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->generator = $generator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->resourceRegistry->all() as $resourceConfig) {
            if (!$resourceConfig->hasBehavior(AceSubjectBehavior::class)) {
                continue;
            }

            $manager = $this
                ->managerFactory
                ->getManager($resourceConfig->getEntityClass());

            $subjects = $this
                ->repositoryFactory
                ->getRepository($resourceConfig->getEntityClass())
                ->findBy(['aclSubjectId' => '']);

            foreach ($subjects as $subject) {
                $output->writeln((string)$subject);

                if (!$subject instanceof AclSubjectInterface) {
                    throw new UnexpectedTypeException($subject, AclSubjectInterface::class);
                }

                if (!$this->generator->generate($subject)) {
                    continue;
                }

                $manager->persist($subject);
            }

            $manager->flush();
        }

        return Command::SUCCESS;
    }
}
