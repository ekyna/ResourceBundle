<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Filesystem;

use DateTime;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToReadFile;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

use function fpassthru;
use function md5;

/**
 * Class FilesystemHelper
 * @package Ekyna\Bundle\ResourceBundle\Service\Filesystem
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FilesystemHelper
{
    private Filesystem $filesystem;
    private int        $streamThreshold;

    private ?PathNormalizer    $pathNormalizer = null;
    private ?FilesystemAdapter $adapter        = null;
    private ?PathPrefixer      $pathPrefixer   = null;

    public function __construct(Filesystem $filesystem, int $streamThreshold = 1014 * 1024)
    {
        $this->filesystem = $filesystem;
        $this->streamThreshold = $streamThreshold;
    }

    /**
     * Creates a download file response for the given path.
     *
     * @throws FilesystemException
     */
    public function createFileResponse(string $path, Request $request = null, bool $inline = true): Response
    {
        if (!$this->filesystem->fileExists($path)) {
            // Local adapter does not throw exception, so let's do it !
            throw UnableToCheckFileExistence::forLocation($path);
        }

        $fileSize = $this->filesystem->fileSize($path);

        if (!$inline && $this->streamThreshold < $fileSize) {
            return $this->createStreamedResponse($path);
        }

        if ($this->isLocal()) {
            return $this->createBinaryFileResponse($path, $inline, $request);
        }

        return $this->createResponse($path, $request);
    }

    /**
     * @throws FilesystemException
     */
    private function createBinaryFileResponse(
        string  $path,
        bool    $inline,
        Request $request = null
    ): BinaryFileResponse {
        $file = $this->getRealPath($path);

        $lastModified = $this->filesystem->lastModified($path);

        $response = new BinaryFileResponse($file);
        $response
            ->setEtag(md5("$path-$lastModified"))
            ->setLastModified(DateTime::createFromFormat('U', (string)$lastModified))
            ->setContentDisposition(
                $inline ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $response->getFile()->getFilename()
            );

        $request && $response->isNotModified($request);

        return $response;
    }

    /**
     * Returns whether the filesystem is local.
     */
    public function isLocal(): bool
    {
        return $this->getAdapter() instanceof LocalFilesystemAdapter;
    }

    public function getRealPath(string $path): string
    {
        if (!$this->isLocal()) {
            throw new LogicException('Can\'t build real file path using remote filesystem.');
        }

        return $this->getPathPrefixer()->prefixPath(
            $this->getPathNormalizer()->normalizePath($path)
        );
    }

    public function fileExists(string $path, bool $throw): bool
    {
        try {
            if (!$this->filesystem->fileExists($path)) {
                throw new UnableToCheckFileExistence($path);
            }
        } catch (FilesystemException $exception) {
            if ($throw) {
                throw $exception;
            }

            return false;
        }

        return true;
    }

    /**
     * @throws FilesystemException
     */
    private function createStreamedResponse(string $path): StreamedResponse
    {
        $response = new StreamedResponse(fn() => fpassthru($this->filesystem->readStream($path)));

        $response->headers->set('Content-Type', $this->filesystem->mimeType($path));
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * @throws FilesystemException
     */
    private function createResponse(string $path, Request $request = null): Response
    {
        $response = new Response();

        $lastModified = $this->filesystem->lastModified($path);

        $response->setEtag(md5("$path-$lastModified"));
        $response->setLastModified(DateTime::createFromFormat('U', $lastModified));
        $response->setPublic();

        if ($request && $response->isNotModified($request)) {
            return $response;
        }

        $response->setContent($this->filesystem->read($path));

        $response->headers->set('Content-Type', $this->filesystem->mimeType($path));

        return $response;
    }

    private function getPathNormalizer(): PathNormalizer
    {
        if ($this->pathNormalizer) {
            return $this->pathNormalizer;
        }

        try {
            $rClass = new ReflectionClass($this->filesystem);
            $rProperty = $rClass->getProperty('pathNormalizer');
            $rProperty->setAccessible(true);

            return $this->pathNormalizer = $rProperty->getValue($this->filesystem);
        } catch (Throwable $exception) {
        }

        throw new UnableToReadFile('Failed to retrieve path normalizer.');
    }

    private function getAdapter(): FilesystemAdapter
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        try {
            $rClass = new ReflectionClass($this->filesystem);
            $rProperty = $rClass->getProperty('adapter');
            $rProperty->setAccessible(true);

            return $this->adapter = $rProperty->getValue($this->filesystem);
        } catch (Throwable $exception) {
        }

        throw new UnableToReadFile('Failed to retrieve adapter.');
    }

    private function getPathPrefixer(): PathPrefixer
    {
        if ($this->pathPrefixer) {
            return $this->pathPrefixer;
        }

        $adapter = $this->getAdapter();

        try {
            $rClass = new ReflectionClass($adapter);
            $rProperty = $rClass->getProperty('prefixer');
            $rProperty->setAccessible(true);

            return $this->pathPrefixer = $rProperty->getValue($adapter);
        } catch (ReflectionException $exception) {
        }

        throw new UnableToReadFile('Failed to retrieve path prefixer.');
    }
}
