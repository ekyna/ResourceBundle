<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\ResourceBundle\Behavior\UploadableBehavior;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\Uploader;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploaderResolver;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToCheckFileExistence;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function sprintf;

#[AsCommand(
    name: 'ekyna:resource:filesystem:check',
    description: 'Verifies that paths stored in the database exists as files in filesystems.'
)]
class FilesystemsCheckCommand extends Command
{
    public function __construct(
        private readonly ResourceRegistryInterface $resourceRegistry,
        private readonly UploaderResolver          $uploaderResolver,
        private readonly ManagerRegistry           $managerRegistry,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->resourceRegistry->all() as $config) {
            if (!$config->hasBehavior(UploadableBehavior::class)) {
                continue;
            }

            $output->writeln($config->getId());

            $this->checkResource($config, $output);
        }

        return Command::SUCCESS;
    }

    private function checkResource(ResourceConfig $config, OutputInterface $output): void
    {
        $id =  $config->getId();
        $class = $config->getEntityClass();

        $filesystem = $this->getFilesystem($class);

        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);
        $table = $metadata->getTableName();
        /** @var Connection $connection */
        $connection = $manager->getConnection();

        $results = $connection->executeQuery("SELECT path FROM $table ORDER BY path ASC");

        foreach ($results->fetchFirstColumn() as $path) {
            try {
                if ($filesystem->fileExists($path)) {
                    continue;
                }
            } catch (FilesystemException|UnableToCheckFileExistence) {
            }

            $output->writeln(sprintf('[%s] %s', $id, $path));
        }
    }

    private function getFilesystem(string $class): FilesystemOperator
    {
        $uploader = $this->uploaderResolver->resolve(new $class());

        $rc = new ReflectionClass(Uploader::class);
        $rp = $rc->getProperty('targetFileSystem');
        $rp->setAccessible(true);

        $filesystem = $rp->getValue($uploader);

        if (!$filesystem instanceof FilesystemOperator) {
            throw new UnexpectedValueException();
        }

        return $filesystem;
    }
}
