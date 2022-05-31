<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerBuilder as ResourceContainerBuilder;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Config\Loader\YamlFileLoader;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaResourceBundle
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaResourceBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @noinspection PhpUnhandledExceptionInspection */
        $loader = $this->loadResources($container);

        $builder = new ResourceContainerBuilder();
        $builder->configure($loader);
        $builder->build($container);

        if ($container->getParameter('kernel.debug')) {
            $container->addObjectResource($builder);
        }

        // Before symfony's register listener pass
        /** @see \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass */
        $container->addCompilerPass(new Compiler\MessengerPass(), PassConfig::TYPE_BEFORE_REMOVING, 1);

        $container->addCompilerPass(new Compiler\ActionAutoConfigurePass());
        $container->addCompilerPass(new Compiler\RouterHostsPass());
        $container->addCompilerPass(new Compiler\RedirectionProviderPass());
        $container->addCompilerPass(new Compiler\UploaderPass());
    }

    /**
     * Loads all resources configuration files.
     *
     * @param ContainerBuilder $container
     *
     * @return ConfigLoader
     * @throws Exception
     */
    private function loadResources(ContainerBuilder $container): ConfigLoader
    {
        $projectDir   = $container->getParameter('kernel.project_dir');
        $configLoader = new ConfigLoader();

        $locator  = new FileLocator([$projectDir]);
        $resolver = new LoaderResolver([
            // TODO new XmlFileLoader($container, $locator),
            new YamlFileLoader($configLoader, $locator),
        ]);

        $fileLoader = new DelegatingLoader($resolver);

        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirname = $bundle['path'] . '/Resources/config';

            /* TODO if ($container->fileExists($file = $dirname.'/resource.xml', false)) {
                $fileRecorder('xml', $file);
            }*/

            if ($container->fileExists($file = $dirname . '/resources.yaml', false)) {
                $fileLoader->load($file);
            }

            if ($container->fileExists($directory = $dirname . '/resources', '/^$/')) {
                $this->loadDirectory($directory, $fileLoader);
            }
        }

        $projectDir = $container->getParameter('kernel.project_dir');
        if ($container->fileExists($directory = $projectDir . '/config/resources', '/^$/')) {
            $this->loadDirectory($directory, $fileLoader);
        }

        // Add container tracked file resources
        foreach ($configLoader->getFiles() as $file) {
            $container->addResource($file);
        }

        return $configLoader;
    }

    /**
     * Loads all configuration files from the given directory.
     *
     * @param string          $directory
     * @param LoaderInterface $loader
     *
     * @throws Exception
     */
    private function loadDirectory(string $directory, LoaderInterface $loader): void
    {
        $files = Finder::create()
            ->followLinks()
            ->files()
            ->in($directory)
            ->name('/\.(xml|yaml)$/')
            ->sortByName();

        foreach ($files as $file) {
            $loader->load($file->getRealPath());
        }
    }
}
