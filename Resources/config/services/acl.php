<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\Behavior\AceSubjectBehavior;
use Ekyna\Bundle\ResourceBundle\Command\AclGenerateSubjectIdCommand;
use Ekyna\Bundle\ResourceBundle\Repository\AceRepository;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclIdGenerator;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclManager;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclManagerInterface;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclVoter;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // ACE repository
        ->set('ekyna_resource.repository.ace', AceRepository::class)
            ->args([
                service('doctrine'),
            ])

        // ACL manager
        ->set('ekyna_resource.acl.manager', AclManager::class)
            ->args([
                service('ekyna_resource.registry.permission'),
                service('ekyna_resource.registry.namespace'),
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.repository.ace'),
                service('doctrine'),
                service('ekyna_resource.cache'),
            ])
        ->alias(AclManagerInterface::class, 'ekyna_resource.acl.manager')

        // ACL security voter
        ->set('ekyna_resource.acl.security_voter', AclVoter::class)
            ->args([
                service('security.access.decision_manager'),
                service('ekyna_resource.acl.manager'),
                service('ekyna_resource.registry.action'),
                service('ekyna_resource.registry.permission'),
            ])
            ->tag('security.voter')

        // ACL id generator
        ->set('ekyna_resource.acl.id_generator', AclIdGenerator::class)

        // ACE subject behavior
        ->set('ekyna_resource.behavior.ace_subject', AceSubjectBehavior::class)
            ->args([
                service('ekyna_resource.acl.id_generator'),
            ])
            ->tag('ekyna_resource.behavior')
        ->alias(AceSubjectBehavior::class, 'ekyna_resource.behavior.ace_subject')

        // ACL generate subject id command
        ->set('ekyna_resource.command.acl_generate_subject_id', AclGenerateSubjectIdCommand::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_resource.acl.id_generator'),
            ])
            ->tag('console.command')
    ;
};
