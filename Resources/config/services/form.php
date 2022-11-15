<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\Form\Extension\DecimalTypeExtension;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\HiddenResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Decimal type extension
    $services->set('ekyna_resource.form_type_extension.decimal', DecimalTypeExtension::class)
        ->tag('form.type_extension');

    // Constant choice form type
    $services->set('ekyna_resource.form_type.constant_choice', ConstantChoiceType::class)
        ->args([
            service('translator'),
        ])
        ->tag('form.type');

    // Hidden resource form type
    $services->set('ekyna_resource.form_type.hidden_resource', HiddenResourceType::class)
        ->args([
            service('ekyna_resource.helper'),
            service('ekyna_resource.repository.factory'),
        ])
        ->tag('form.type');

    // Locale choice form type
    $services->set('ekyna_resource.form_type.locale_choice', LocaleChoiceType::class)
        ->args([
            param('ekyna_resource.locales'),
        ])
        ->tag('form.type');

    // Resource choice form type
    $services->set('ekyna_resource.form_type.resource_choice', ResourceChoiceType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('form.type');

    // Resource search form type
    $services->set('ekyna_resource.form_type.resource_search', ResourceSearchType::class)
        ->args([
            service('ekyna_resource.helper'),
            service('ekyna_resource.repository.factory'),
            service('serializer'),
            service('translator'),
        ])
        ->tag('form.type')
        ->tag('form.js', [
            'selector' => '.resource-search',
            'path'     => 'ekyna-resource/form/resource-search',
        ]);
};
