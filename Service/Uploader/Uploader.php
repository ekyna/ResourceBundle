<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Uploader;

use Ekyna\Bundle\ResourceBundle\Exception\UploadException;
use Ekyna\Component\Resource\Model\UploadableInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

use function array_pop;
use function bin2hex;
use function count;
use function explode;
use function fclose;
use function file_exists;
use function filesize;
use function fopen;
use function implode;
use function pathinfo;
use function random_bytes;
use function sprintf;
use function substr;
use function unlink;

use const PATHINFO_DIRNAME;

/**
 * Class Uploader
 * @package Ekyna\Bundle\ResourceBundle\Service\Uploader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Uploader implements UploaderInterface
{
    protected FilesystemOperator $sourceFilesystem;
    protected FilesystemOperator $targetFileSystem;


    /**
     * Constructor.
     *
     * @param FilesystemOperator $sourceFilesystem
     * @param FilesystemOperator $targetFilesystem
     */
    public function __construct(FilesystemOperator $sourceFilesystem, FilesystemOperator $targetFilesystem)
    {
        $this->sourceFilesystem = $sourceFilesystem;
        $this->targetFileSystem = $targetFilesystem;
    }

    /**
     * @inheritDoc
     *
     * @throws UploadException
     */
    public function prepare(UploadableInterface $uploadable): void
    {
        if (!($uploadable->hasFile() || $uploadable->hasKey() || $uploadable->shouldBeRenamed())) {
            return;
        }

        $uploadable->setOldPath($uploadable->getPath());
        $uploadable->setPath($this->generatePath($uploadable->guessFilename()));

        // By File
        if ($uploadable->hasFile()) {
            $file = $uploadable->getFile();

            if (!file_exists($file->getRealPath())) {
                throw new UploadException(sprintf('Source file "%s" does not exists.', $file->getRealPath()));
            }

            $uploadable->setSize(filesize($file->getRealPath()));

            return;
        }

        // By Key
        if ($uploadable->hasKey()) {
            $sourceKey = $uploadable->getKey();

            try {
                if (!$this->sourceFilesystem->fileExists($sourceKey)) {
                    throw new UploadException(sprintf('Source file "%s" does not exists.', $sourceKey));
                }
            } catch (FilesystemException $exception) {
                throw new UploadException(sprintf('Source file "%s" does not exists.', $sourceKey), 0, $exception);
            }

            try {
                $size = $this->sourceFilesystem->fileSize($sourceKey);
            } catch (FilesystemException $exception) {
                throw new UploadException(
                    sprintf('Failed to get size of file "%s".', $sourceKey),
                    0,
                    $exception
                );
            }

            $uploadable->setSize($size);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws UploadException
     */
    public function upload(UploadableInterface $uploadable): void
    {
        if (!$uploadable->hasPath()) {
            return;
        }

        $targetKey = $uploadable->getPath();

        // By file
        if ($uploadable->hasFile()) {
            $sourcePath = $uploadable->getFile()->getRealPath();

            if (false === $stream = fopen($sourcePath, 'r+')) {
                throw new UploadException(sprintf('Failed to open file "%s".', $sourcePath));
            }

            try {
                $this->targetFileSystem->writeStream($targetKey, $stream);
            } catch (FilesystemException $exception) {
                throw new UploadException(
                    sprintf('Failed to copy file from "%s" to "%s".', $sourcePath, $targetKey),
                    0,
                    $exception
                );
            }

            fclose($stream);
            unlink($sourcePath);

            $uploadable->setFile(null);

            return;
        }

        // By key
        if ($uploadable->hasKey()) {
            $sourceKey = $uploadable->getKey();

            try {
                $stream = $this->sourceFilesystem->readStream($sourceKey);
            } catch (FilesystemException $exception) {
                throw new UploadException(
                    sprintf('Failed to open file "%s".', $sourceKey),
                    0,
                    $exception
                );
            }

            try {
                $this->targetFileSystem->writeStream($targetKey, $stream);
            } catch (FilesystemException $exception) {
                throw new UploadException(
                    sprintf('Failed to copy file from "%s" to "%s".', $sourceKey, $targetKey),
                    0,
                    $exception
                );
            }

            fclose($stream);

            try {
                $this->sourceFilesystem->delete($sourceKey);
            } catch (FilesystemException $exception) {
            }

            $this->cleanUp($this->sourceFilesystem, $sourceKey);

            $uploadable->setKey(null);

            return;
        }

        // Rename
        if ($uploadable->hasOldPath()) {
            $sourcePath = $uploadable->getOldPath();
            $targetPath = $uploadable->getPath();

            try {
                $this->targetFileSystem->move($sourcePath, $targetPath);
            } catch (FilesystemException $exception) {
                $message = sprintf(
                    'Failed to rename file from "%s" to "%s".',
                    $sourcePath,
                    $targetPath
                );

                throw new UploadException($message, 0, $exception);
            }

            $this->cleanUp($this->targetFileSystem, $sourcePath);

            $uploadable->setOldPath(null);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws UploadException
     */
    public function remove(UploadableInterface $uploadable): void
    {
        if (empty($path = $uploadable->getOldPath())) {
            return;
        }

        try {
            if (!$this->targetFileSystem->fileExists($path)) {
                throw new UploadException(sprintf('File "%s" not found.', $path));
            }
        } catch (FilesystemException $exception) {
            throw new UploadException(sprintf('File "%s" not found.', $path), 0, $exception);
        }

        try {
            $this->targetFileSystem->delete($path);
        } catch (FilesystemException $exception) {
            throw new UploadException(sprintf('Failed to delete file "%s".', $path), 0, $exception);
        }

        $this->cleanUp($this->targetFileSystem, $path);

        $uploadable->setOldPath(null);
    }

    /**
     * Generates a unique path.
     *
     * @param string $filename
     *
     * @return string
     */
    private function generatePath(string $filename): string
    {
        do {
            /** @noinspection PhpUnhandledExceptionInspection */
            $hash = bin2hex(random_bytes(3));
            $path = sprintf(
                '%s/%s/%s',
                substr($hash, 0, 3),
                substr($hash, 3),
                $filename
            );

            try {
                if ($this->targetFileSystem->fileExists($path)) {
                    continue;
                }
            } catch (FilesystemException $exception) {
            }

            break;
        } while (true);

        return $path;
    }

    /**
     * Removes the path directories if they are empty.
     *
     * @param FilesystemOperator $filesystem
     * @param string             $path
     */
    private function cleanUp(FilesystemOperator $filesystem, string $path): void
    {
        $parts = explode('/', pathinfo($path, PATHINFO_DIRNAME));

        while (0 < count($parts)) {
            $key = implode('/', $parts);

            try {
                $filesystem->deleteDirectory($key);
            } catch (FilesystemException $exception) {
                return;
            }

            array_pop($parts);
        }
    }
}
