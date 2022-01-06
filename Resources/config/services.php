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
use Ekyna\Bundle\ResourceBundle\Service\Error\ErrorReporter;
use Ekyna\Bundle\ResourceBundle\Service\Http\TagManager;
use Ekyna\Bundle\ResourceBundle\Service\Redirection\ProviderRegistry;
use Ekyna\Bundle\ResourceBundle\Service\Routing\ResourceLoader;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploaderResolver;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploadToggler;
use Ekyna\Bundle\ResourceBundle\Table\Column\DecimalColumnTypeExtension;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Copier\Copier;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Helper\PdfGenerator;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Context factory
        ->set('ekyna_resource.factory.context', ContextFactory::class)
            ->args([
                service('request_stack'),
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.repository.factory'),
            ])

        // Action listener
        ->set('ekyna_resource.listener.action', ActionListener::class)
            ->args([
                service('ekyna_resource.registry.action'),
                service('ekyna_resource.factory.context'),
                service('security.authorization_checker'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController'])

        // Resource helper
        ->set('ekyna_resource.helper', ResourceHelper::class)
            ->args([
                service('ekyna_resource.registry.action'),
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_resource.event_dispatcher'),
                service('ekyna_resource.factory.context'),
                service('security.authorization_checker'),
                service('router'),
            ])
            ->tag('twig.runtime')

        // Resource copier
        ->set('ekyna_resource.copier', Copier::class)
            ->alias(CopierInterface::class, 'ekyna_resource.copier')

        // Redirections
        ->set('ekyna_resource.redirection.provider_registry', ProviderRegistry::class)

        // Error reporter
        ->set('ekyna_resource.reporter.error', ErrorReporter::class)
            ->args([
                service('security.token_storage'),
                service('twig'),
                service('mailer.mailer'),
                param('ekyna_resource.report_email'),
            ])

        // Kernel exception listener
        ->set('ekyna_resource.listener.kernel_exception', KernelExceptionListener::class)
            ->args([
                service('ekyna_resource.redirection.provider_registry'),
                service('security.http_utils'),
                service('ekyna_resource.reporter.error'),
                service('request_stack'),
                param('kernel.debug'),
            ])
            ->tag('kernel.event_listener', [
                'priority' => 2, // Just before \Symfony\Component\Security\Http\Firewall\ExceptionListener::onKernelException
            ])

        // Cache
        ->set('ekyna_resource.cache')
            ->parent('cache.app')
            ->private()
            ->tag('cache.pool', ['clearer' => 'cache.default_clearer'])

        // "Is default" behavior
        ->set('ekyna_resource.behavior.is_default', IsDefaultBehavior::class)
            ->tag('ekyna_resource.behavior')
        ->alias(IsDefaultBehavior::class, 'ekyna_resource.behavior.is_default')

        // Resource table filter type
        ->set('ekyna_resource.table_column_type_extension.decimal', DecimalColumnTypeExtension::class)
            ->tag('table.column_type_extension')

        // Resource table filter type
        ->set('ekyna_resource.table_filter_type.resource', ResourceType::class)
            ->args([
                service('ekyna_resource.registry.resource'),
            ])
            ->tag('table.filter_type')

        // Abstract resource routing loader
        ->set('ekyna_resource.routing.resource_loader', ResourceLoader::class)
            ->abstract()
            ->call('setNamespaceRegistry', [service('ekyna_resource.registry.namespace')])
            ->call('setResourceRegistry', [service('ekyna_resource.registry.resource')])
            ->call('setActionRegistry', [service('ekyna_resource.registry.action')])

        // Abstract normalizer
        ->set('ekyna_resource.normalizer.abstract', ResourceNormalizer::class)
            ->abstract(true)
            ->call('setNameConverter', [service('serializer.name_converter.camel_case_to_snake_case')])
            ->call('setPropertyAccessor', [service('serializer.property_accessor')])

        // Http tag manager
        ->set('ekyna_resource.http.tag_manager', TagManager::class)

        // Uploader resolver
        ->set('ekyna_resource.uploader_resolver', UploaderResolver::class)
            ->args([
                // Replaced by compiler pass
                abstract_arg('Uploaders services locator'),
            ])

        // Upload toggler
        ->set('ekyna_resource.upload_toggler', UploadToggler::class)

        // Upload event subscriber
        ->set('ekyna_resource.listener.oneup_upload', OneupUploadListener::class)
            ->tag('kernel.event_subscriber')

        // Upload event subscriber
        ->set('ekyna_resource.listener.uploadable', UploadableListener::class)
            ->args([
                service('ekyna_resource.uploader_resolver'),
                service('ekyna_resource.upload_toggler'),
            ])
            // Tags added by UploadableBehavior

        // Filesystems aliases
        ->alias('ekyna_resource.filesystem.tmp', 'oneup_flysystem.local_tmp_filesystem')
        ->alias('ekyna_resource.filesystem.upload', 'oneup_flysystem.local_upload_filesystem')

        // Local upload controller
        ->set('ekyna_resource.controller.local_upload', LocalUploadController::class)
            ->args([
                service('oneup_flysystem.local_upload_filesystem'),
            ])
        ->alias(LocalUploadController::class, 'ekyna_resource.controller.local_upload')->public()

        // PDF Generator
        ->set('ekyna_resource.generator.pdf', PdfGenerator::class)
            ->args([
                abstract_arg('PDF generator endpoint'),
                abstract_arg('PDF generator token'),
            ])
    ;
};
