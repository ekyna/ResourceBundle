<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\Behavior\IsDefaultBehavior;
use Ekyna\Bundle\ResourceBundle\Controller\LocalUploadController;
use Ekyna\Bundle\ResourceBundle\EventListener\ActionListener;
use Ekyna\Bundle\ResourceBundle\EventListener\KernelExceptionListener;
use Ekyna\Bundle\ResourceBundle\EventListener\OneupUploadListener;
use Ekyna\Bundle\ResourceBundle\EventListener\UploadableListener;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\ContextFactory;
use Ekyna\Bundle\ResourceBundle\Service\Http\TagManager;
use Ekyna\Bundle\ResourceBundle\Service\Redirection\ProviderRegistry;
use Ekyna\Bundle\ResourceBundle\Service\Routing\ResourceLoader;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploaderResolver;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploadToggler;
use Ekyna\Bundle\ResourceBundle\Table\Column\DecimalColumnTypeExtension;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Twig\ResourceExtension;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Copier\Copier;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Helper\EnumHelper;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Ekyna\Component\Resource\Import\CsvImporter;
use Ekyna\Component\Resource\Message\MessageQueue;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Context factory
    $services->set('ekyna_resource.factory.context', ContextFactory::class)
        ->args([
            service('request_stack'),
            service('ekyna_resource.registry.resource'),
            service('ekyna_resource.repository.factory'),
        ]);

    // Action listener
    $services->set('ekyna_resource.listener.action', ActionListener::class)
        ->args([
            service('ekyna_resource.registry.action'),
            service('ekyna_resource.factory.context'),
            service('security.authorization_checker'),
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController']);

    // Resource helper
    $services->set('ekyna_resource.helper', ResourceHelper::class)
        ->args([
            service('ekyna_resource.registry.action'),
            service('ekyna_resource.registry.resource'),
            service('ekyna_resource.manager.factory'),
            service('ekyna_resource.event_dispatcher'),
            service('ekyna_resource.factory.context'),
            service('security.authorization_checker'),
            service('router'),
        ])
        ->tag('twig.runtime'); // TODO Remove as not used

    // Resource copier
    $services->set('ekyna_resource.copier', Copier::class)
        ->alias(CopierInterface::class, 'ekyna_resource.copier');

    // Resource CSV importer
    $services->set('ekyna_resource.importer.csv', CsvImporter::class)
        ->args([
            service('ekyna_resource.factory.factory'),
            service('ekyna_resource.repository.factory'),
            service('validator'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.terminate', 'method' => 'flush'])
        ->alias(CsvImporter::class, 'ekyna_resource.importer.csv');

    // Redirections
    $services->set('ekyna_resource.redirection.provider_registry', ProviderRegistry::class);

    // Kernel exception listener
    $services->set('ekyna_resource.listener.kernel_exception', KernelExceptionListener::class)
        ->args([
            service('ekyna_resource.redirection.provider_registry'),
            service('security.http_utils'),
            service('request_stack'),
        ])
        ->tag('kernel.event_listener', [
            'event'    => KernelEvents::EXCEPTION,
            'priority' => 2,
            // Just before \Symfony\Component\Security\Http\Firewall\ExceptionListener::onKernelException
        ]);

    // Cache
    $services->set('ekyna_resource.cache')
        ->parent('cache.app')
        ->private()
        ->tag('cache.pool', ['clearer' => 'cache.default_clearer']);

    // "Is default" behavior
    $services->set('ekyna_resource.behavior.is_default', IsDefaultBehavior::class)
        ->tag('ekyna_resource.behavior')
        ->alias(IsDefaultBehavior::class, 'ekyna_resource.behavior.is_default');

    // Resource table filter type
    $services->set('ekyna_resource.table_column_type_extension.decimal', DecimalColumnTypeExtension::class)
        ->tag('table.column_type_extension');

    // Resource table filter type
    $services->set('ekyna_resource.table_filter_type.resource', ResourceType::class)
        ->args([
            service('ekyna_resource.registry.resource'),
        ])
        ->tag('table.filter_type');

    // Abstract resource routing loader
    $services->set('ekyna_resource.routing.resource_loader', ResourceLoader::class)
        ->abstract()
        ->call('setNamespaceRegistry', [service('ekyna_resource.registry.namespace')])
        ->call('setResourceRegistry', [service('ekyna_resource.registry.resource')])
        ->call('setActionRegistry', [service('ekyna_resource.registry.action')]);

    // Abstract normalizer
    $services->set('ekyna_resource.normalizer.abstract', ResourceNormalizer::class)
        ->abstract()
        ->call('setNameConverter', [service('serializer.name_converter.camel_case_to_snake_case')])
        ->call('setPropertyAccessor', [service('serializer.property_accessor')]);

    // Http tag manager
    $services->set('ekyna_resource.http.tag_manager', TagManager::class);

    // Uploader resolver
    $services->set('ekyna_resource.uploader_resolver', UploaderResolver::class)
        ->args([
            // Replaced by compiler pass
            abstract_arg('Uploaders services locator'),
        ]);

    // Upload toggler
    $services->set('ekyna_resource.upload_toggler', UploadToggler::class);

    // Upload event subscriber
    $services->set('ekyna_resource.listener.oneup_upload', OneupUploadListener::class)
        ->tag('kernel.event_subscriber');

    // Upload event subscriber
    $services->set('ekyna_resource.listener.uploadable', UploadableListener::class)
        ->args([
            service('ekyna_resource.uploader_resolver'),
            service('ekyna_resource.upload_toggler'),
        ])
        // Tags added by UploadableBehavior

        // Filesystems aliases
        ->alias('ekyna_resource.filesystem.tmp', 'oneup_flysystem.local_tmp_filesystem')
        ->alias('ekyna_resource.filesystem.upload', 'oneup_flysystem.local_upload_filesystem');

    // Local upload controller
    $services->set('ekyna_resource.controller.local_upload', LocalUploadController::class)
        ->args([
            service('oneup_flysystem.local_upload_filesystem'),
        ])
        ->alias(LocalUploadController::class, 'ekyna_resource.controller.local_upload')->public();

    // PDF Generator
    $services->set('ekyna_resource.generator.pdf', PdfGenerator::class)
        ->args([
            abstract_arg('PDF generator endpoint'),
            abstract_arg('PDF generator token'),
        ]);

    // Message queue
    $services->set('ekyna_resource.queue.message', MessageQueue::class)
        ->args([
            service('messenger.default_bus'),
        ])
        ->tag('kernel.event_listener', [
            'event'    => KernelEvents::TERMINATE,
            'method'   => 'flush',
            'priority' => -2048,
        ])
        ->tag('kernel.event_listener', [
            'event'    => ConsoleEvents::TERMINATE,
            'method'   => 'flush',
            'priority' => -2048,
        ])
        ->tag('kernel.event_listener', [
            'event'    => WorkerMessageHandledEvent::class,
            'method'   => 'flush',
            'priority' => -2048,
        ]);

    // Enum helper
    $services->set('ekyna_resource.helper.enum', EnumHelper::class)
        ->args([
            service('translator'),
        ])
        ->tag('twig.runtime');

    // Twig extension
    $services->set('ekyna_resource.twig.extension', ResourceExtension::class)
        ->tag('twig.extension');
};
