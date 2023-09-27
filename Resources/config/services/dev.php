<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\DataFixtures\EventFixturesLoaderDecorator;
use Ekyna\Bundle\ResourceBundle\DataFixtures\ResourceProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services
        ->set('ekyna_commerce', ResourceProvider::class)
        ->args([
            service('ekyna_resource.repository.factory'),
        ])
        ->tag('nelmio_alice.faker.provider');

    $services
        ->set(EventFixturesLoaderDecorator::class)
        ->decorate('hautelook_alice.loader')
        ->args([
            service('.inner'),
            service('event_dispatcher'),
        ]);
};
