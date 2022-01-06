<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ResourceBundle\Action;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler\ActionAutoConfigurePass as BasePass;

/**
 * Class ActionAutoConfigurePass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionAutoConfigurePass extends BasePass
{
    protected function getAutoconfigureMap(): array
    {
        return [
            Action\AuthorizationTrait::class           => [
                'setAuthorizationChecker' => 'security.authorization_checker',
            ],
            Action\CopierTrait::class                  => [
                'setCopier' => 'ekyna_resource.copier',
            ],
            Action\FactoryTrait::class                 => [
                'setFactoryFactory' => 'ekyna_resource.factory.factory',
            ],
            Action\FormTrait::class                    => [
                'setFormFactory' => 'form.factory',
            ],
            Action\HelperTrait::class                  => [
                'setResourceHelper' => 'ekyna_resource.helper',
            ],
            Action\ManagerTrait::class                 => [
                'setManagerFactory' => 'ekyna_resource.manager.factory',
            ],
            Action\RegistryTrait::class                => [
                'setRegistryFactory' => 'ekyna_resource.config.registry_factory',
            ],
            Action\RepositoryTrait::class              => [
                'setRepositoryFactory' => 'ekyna_resource.repository.factory',
            ],
            Action\ResourceEventDispatcherTrait::class => [
                'setResourceEventDispatcher' => 'ekyna_resource.event_dispatcher',
            ],
            Action\SearchTrait::class                  => [
                'setSearchRepositoryFactory' => 'ekyna_resource.factory.search_repository',
            ],
            Action\SerializerTrait::class              => [
                'setSerializer' => 'serializer',
            ],
            Action\SessionTrait::class                 => [
                'setRequestStack' => 'request_stack',
            ],
            Action\TemplatingTrait::class              => [
                'setEnvironment' => 'twig',
            ],
            Action\TranslatorTrait::class              => [
                'setTranslator' => 'translator',
            ],
            Action\UrlGeneratorTrait::class            => [
                'setUrlGenerator' => 'router',
            ],
            Action\ValidatorTrait::class               => [
                'setValidator' => 'validator',
            ],
        ];
    }
}
