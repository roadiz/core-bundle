# Roadiz Core bundle

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/core-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/core-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\CoreBundle\RoadizCoreBundle::class => ['all' => true],
];
```

## Configuration


- Add rate limiter configuration for Webhooks
```yaml
framework:
    rate_limiter:
        throttled_webhooks:
            policy: 'token_bucket'
            limit: 1
            rate: { interval: '10 seconds'}
```
- Add Doctrine configuration for entities
```yaml
doctrine:
    orm:
        auto_generate_proxy_classes: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            RoadizCoreBundle:
                is_bundle: true
                type: annotation
                dir: 'Entity'
                prefix: 'RZ\Roadiz\CoreBundle\Entity'
                alias: RoadizCoreBundle
            RZ\Roadiz\Core:
                is_bundle: false
                type: annotation
                dir: '%kernel.project_dir%/vendor/roadiz/models/src/Roadiz/Core/AbstractEntities'
                prefix: 'RZ\Roadiz\Core\AbstractEntities'
                alias: AbstractEntities
            GeneratedNodeSources:
                is_bundle: false
                type: annotation
                dir: '%kernel.project_dir%/generated'
                prefix: 'GeneratedNodeSources'
                alias: GeneratedNodeSources
```
