<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\DataFixtures\ResourceProvider;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->set('ekyna_commerce', ResourceProvider::class)
            ->args([
                service('ekyna_resource.repository.factory'),
            ])
            ->tag('nelmio_alice.faker.provider')
    ;
};
