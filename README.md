ResourceBundle
==========

Resource component integration.

## Installation

Install through composer:

    composer require ekyna/resource-bundle:^8.0

Register the bundle:

```php
// config/bundles.php
<?php

return [
    // ...

    Ekyna\Bundle\ResourceBundle\EkynaResourceBundle::class => ['all' => true],
];

```

## Configuration

```yaml
# config/packages/ekyna_resource.yaml
ekyna_resource:
    i18n:
        # Default locale
        locale: en
        # Available locales
        locales:
            - en
            - es
            - fr
        # Localized hosts
        hosts:
            en: en.sf.local
            es: es.sf.local
            fr: fr.sf.local
```

This bundle will prepend the following symfony framework configuration:

```yaml
framework:
    default_locale: en
    trusted_hosts:
        - en.sf.local
        - es.sf.local
        - fr.sf.local
    translator:
        enabled_locales: [en, es, fr]
        fallbacks: [en]
    router:
        default_uri: sf.en.local
```

## Routing

Adding the ```ekyna_i18n``` option to route or routes collections, will automatically configure localized hosts.

```yaml
app_about:
    path:
        en: /about
        fr: /a-propos
    controller: App\Controller\I18nController::test
    options:
        ekyna_i18n: ~
```
