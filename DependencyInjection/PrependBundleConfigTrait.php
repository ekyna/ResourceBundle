<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

use function array_key_exists;
use function dirname;
use function file_get_contents;
use function is_array;
use function is_dir;
use function realpath;
use function sprintf;

/**
 * Trait PrependBundleConfigTrait
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait PrependBundleConfigTrait
{
    /**
     * Prepends the bundle's configuration files.
     *
     * @param ContainerBuilder $container
     * @param string           $directory
     */
    public function prependBundleConfigFiles(
        ContainerBuilder $container,
        string $directory = '/../Resources/config/prepend'
    ): void {
        $directory = $this->getConfigurationDirectory($directory);
        $this->prependBundleConfigDirectory($container, $directory);

        // Environment specific
        $directory = $directory . '/' . $container->getParameter('kernel.environment');
        if (!is_dir($directory)) {
            return;
        }
        $this->prependBundleConfigDirectory($container, $directory);
    }

    /**
     * Prepends the bundles configuration files from the given directory.
     *
     * @param ContainerBuilder $container
     * @param string           $directory
     */
    private function prependBundleConfigDirectory(ContainerBuilder $container, string $directory)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $finder = new Finder();
        $parser = new Parser();

        foreach ($finder->in($directory)->files()->name('*.yaml') as $file) {
            $bundle = $file->getBasename('.yaml');

            if (!array_key_exists($bundle, $bundles)) {
                continue;
            }

            $path = $file->getRealPath();

            $configs = $parser->parse(file_get_contents($path));
            if (!is_array($configs)) {
                throw new RuntimeException('Failed to parse ' . $path);
            }

            $container->addResource(new FileResource($path));
            foreach ($configs as $key => $config) {
                $container->prependExtensionConfig($key, $config);
            }
        }

        // TODO Prepend PHP Config
    }

    /**
     * Returns the configuration directory.
     *
     * @param string $directory
     *
     * @return string
     */
    private function getConfigurationDirectory(string $directory): string
    {
        $reflector = new ReflectionClass($this);
        $fileName = $reflector->getFileName();

        if (!$directory = realpath($path = (dirname($fileName) . $directory))) {
            throw new RuntimeException(sprintf('The configuration directory "%s" does not exists.', $path));
        }

        if (!is_dir($directory)) {
            throw new RuntimeException(sprintf('The configuration directory "%s" does not exists.', $directory));
        }

        return $directory;
    }
}
