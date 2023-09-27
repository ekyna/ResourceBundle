<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\LoaderInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventFixturesLoaderDecorator
 * @package Ekyna\Bundle\ResourceBundle\DataFixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EventFixturesLoaderDecorator implements LoaderInterface
{
    public function __construct(
        private readonly LoaderInterface          $loader,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function load(
        Application            $application,
        EntityManagerInterface $manager,
        array                  $bundles,
        string                 $environment,
        bool                   $append,
        bool                   $purgeWithTruncate,
        bool                   $noBundles = false
    ): array {
        $this->dispatcher->dispatch(new Event\FixturesLoadingStart());

        $results = $this->loader->load($application,
            $manager,
            $bundles,
            $environment,
            $append,
            $purgeWithTruncate,
            $noBundles
        );

        $this->dispatcher->dispatch(new Event\FixturesLoadingEnd());

        return $results;
    }
}
