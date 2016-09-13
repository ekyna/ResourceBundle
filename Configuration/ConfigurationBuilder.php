<?php

namespace Ekyna\Bundle\ResourceBundle\Configuration;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Bundle\AdminBundle\Controller;
use Ekyna\Bundle\AdminBundle\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Configuration\Configuration;
use Ekyna\Component\Resource\Doctrine\ORM;
use Ekyna\Component\Resource\Operator;
use Ekyna\Component\Table\TableTypeInterface;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurationBuilder
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @todo rename: This is much more than a "configuration builder"
 * @todo move: In DI folder
 */
class ConfigurationBuilder
{
    const DEFAULT_CONTROLLER   = Controller\ResourceController::class;
    const CONTROLLER_INTERFACE = Controller\ResourceControllerInterface::class;

    const DEFAULT_OPERATOR     = ORM\Operator\ResourceOperator::class;
    const OPERATOR_INTERFACE   = Operator\ResourceOperatorInterface::class;

    const DEFAULT_REPOSITORY   = ORM\ResourceRepository::class;
    const REPOSITORY_INTERFACE = ORM\ResourceRepositoryInterface::class;

    const TRANSLATABLE_DEFAULT_REPOSITORY   = ORM\TranslatableResourceRepository::class;
    const TRANSLATABLE_REPOSITORY_INTERFACE = ORM\TranslatableResourceRepositoryInterface::class;

    const FORM_INTERFACE       = FormTypeInterface::class;
    const TABLE_INTERFACE      = TableTypeInterface::class;
    const EVENT_INTERFACE      = ResourceEventInterface::class;

    const CONFIGURATION        = Configuration::class;
    const CLASS_METADATA       = ClassMetadata::class;

    /**
     * @var OptionsResolver
     */
    static private $optionsResolver;

    /**
     * @var DI\ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $resourceId;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param DI\ContainerBuilder $container
     */
    public function __construct(DI\ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Configures the pool builder.
     *
     * @param string $namespace
     * @param string $resourceId
     * @param array  $options
     *
     * @throws \RuntimeException
     *
     * @return ConfigurationBuilder
     */
    public function configure($namespace, $resourceId, array $options)
    {
        if (!(preg_match('~^[a-z_]+$~', $namespace))) {
            throw new \RuntimeException(sprintf('Bad namespace format "%s" (underscore expected).', $namespace));
        }
        if (!(preg_match('~^[a-z_]+$~', $resourceId))) {
            throw new \RuntimeException(sprintf('Bad resource id format "%s" (underscore expected).', $resourceId));
        }

        $this->namespace = $namespace;
        $this->resourceId = $resourceId;
        $this->options = $this->getOptionsResolver()->resolve($options);

        return $this;
    }

    /**
     * Builds the container.
     *
     * @return ConfigurationBuilder
     */
    public function build()
    {
        $this->createEntityClassParameter();

        $this->createConfigurationDefinition();

        $this->createMetadataDefinition();
        $this->createManagerDefinition();
        $this->createRepositoryDefinition();
        $this->createOperatorDefinition();

        // TODO search repository service

        $this->createControllerDefinition();

        $this->createFormDefinition();
        $this->createTableDefinition();

        $this->configureTranslations();

        return $this;
    }

    /**
     * Returns the options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        if (null === self::$optionsResolver) {
            $classExists = function ($class) {
                if (!class_exists($class)) {
                    throw new InvalidOptionsException(sprintf('Class %s does not exists.', $class));
                }
                return true;
            };
            $classExistsAndImplements = function($class, $interface) use ($classExists) {
                $classExists($class);
                if (!in_array($interface, class_implements($class))) {
                    throw new InvalidOptionsException(sprintf('Class %s must implement %s.', $class, $interface));
                }
                return true;
            };
            $validOperator = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::OPERATOR_INTERFACE);
            };
            $validController = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::CONTROLLER_INTERFACE);
            };
            $validForm = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::FORM_INTERFACE);
            };
            $validTable = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::TABLE_INTERFACE);
            };
            $validEvent = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::EVENT_INTERFACE);
            };

            self::$optionsResolver = new OptionsResolver();
            /** @noinspection PhpUnusedParameterInspection */
            self::$optionsResolver
                ->setDefaults([
                    'entity'      => null,
                    'repository'  => null,
                    'operator'    => self::DEFAULT_OPERATOR,
                    'controller'  => self::DEFAULT_CONTROLLER,
                    'templates'   => null,
                    'form'        => null,
                    'table'       => null,
                    'event'       => null,
                    'parent'      => null,
                    'translation' => null,
                ])

                ->setAllowedTypes('entity',      'string')
                ->setAllowedTypes('repository',  ['null', 'string'])
                ->setAllowedTypes('operator',    'string')
                ->setAllowedTypes('controller',  'string')
                ->setAllowedTypes('templates',   ['null', 'string', 'array'])
                ->setAllowedTypes('form',        ['null', 'string'])
                ->setAllowedTypes('table',       ['null', 'string'])
                ->setAllowedTypes('event',       ['null', 'string'])
                ->setAllowedTypes('parent',      ['null', 'string'])
                ->setAllowedTypes('translation', ['null', 'array'])

                ->setAllowedValues('entity',     $classExists)
                ->setAllowedValues('operator',   $validOperator)
                ->setAllowedValues('controller', $validController)
                ->setAllowedValues('form',       $validForm)
                ->setAllowedValues('table',      $validTable)
                ->setAllowedValues('event',      $validEvent)

                ->setNormalizer('repository', function(Options $options, $value) use ($classExistsAndImplements) {
                    $translatable = is_array($options['translation']);
                    $interface = $translatable ? self::TRANSLATABLE_REPOSITORY_INTERFACE : self::REPOSITORY_INTERFACE;
                    if (null === $value) {
                        if ($translatable) {
                            $value = self::TRANSLATABLE_DEFAULT_REPOSITORY;
                        } else {
                            $value = self::DEFAULT_REPOSITORY;
                        }
                    }
                    $classExistsAndImplements($value, $interface);
                    return $value;
                })
                ->setNormalizer('translation', function (Options $options, $value) use ($classExistsAndImplements) {
                    if (is_array($value)) {
                        if (!array_key_exists('entity', $value)) {
                            throw new InvalidOptionsException('translation.entity must be defined.');
                        }
                        if (!array_key_exists('fields', $value)) {
                            throw new InvalidOptionsException('translation.fields must be defined.');
                        }
                        if (!is_array($value['fields']) || empty($value['fields'])) {
                            throw new InvalidOptionsException('translation.fields can\'t be empty.');
                        }
                        if (!array_key_exists('repository', $value)) {
                            $value['repository'] = self::DEFAULT_REPOSITORY;
                        }
                        $classExistsAndImplements($value['repository'], self::REPOSITORY_INTERFACE);
                    }
                    return $value;
                })
                // TODO templates normalization ?
            ;
        }
        return self::$optionsResolver;
    }

    /**
     * Creates the entity class parameter.
     */
    private function createEntityClassParameter()
    {
        $id = $this->getServiceId('class');
        if (!$this->container->hasParameter($id)) {
            $this->container->setParameter($id, $this->options['entity']);
        }

        $this->configureInheritanceMapping(
            $this->namespace.'.'.$this->resourceId,
            $this->options['entity'],
            $this->options['repository']
        );
    }

    /**
     * Creates the Configuration service definition.
     */
    private function createConfigurationDefinition()
    {
        $id = $this->getServiceId('configuration');
        if (!$this->container->has($id)) {
            $config = [
                'namespace' => $this->namespace,
                'id'        => $this->resourceId,
                'name'      => Inflector::camelize($this->resourceId),
                'parent_id' => $this->options['parent'],
                'classes'   => [
                    'resource'  => $this->options['entity'],
                    'form_type' => $this->getServiceClass('form'), // TODO
                    'event'     => $this->options['event'],
                ],
                'templates' => $this->options['templates'],
                //'templates' => $this->buildTemplateList($this->options['templates']),
            ];

            $definition = new DI\Definition(self::CONFIGURATION);
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.configuration_factory'), 'createConfiguration'])
                ->setArguments([$config])
                ->addTag('ekyna_resource.configuration', [
                    'alias' => sprintf('%s_%s', $this->namespace, $this->resourceId)]
                )
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Builds the templates list.
     *
     * @param mixed $templatesConfig
     * @return array
     */
    /*private function buildTemplateList($templatesConfig)
    {
        $templateNamespace = self::DEFAULT_TEMPLATES;
        if (is_string($templatesConfig)) {
            $templateNamespace = $templatesConfig;
        }
        $templatesList = [];
        foreach (self::$templates as $name => $extensions) {
            foreach ($extensions as $extension) {
                $file = $name.'.'.$extension;
                $templatesList[$file] = $templateNamespace.':'.$file;
            }
        }
        // TODO add resources controller traits templates ? (like new_child.html)
        if (is_array($templatesConfig)) {
            $templatesList = array_merge($templatesList, $templatesConfig);
        }
        return $templatesList;
    }*/

    /**
     * Creates the Table service definition.
     */
    private function createMetadataDefinition()
    {
        $id = $this->getServiceId('metadata');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition(self::CLASS_METADATA);
            $definition
                ->setFactory([new DI\Reference($this->getManagerServiceId()), 'getClassMetadata'])
                ->setArguments([
                    $this->container->getParameter($this->getServiceId('class'))
                ])//->setPublic(false)
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the manager definition.
     */
    private function createManagerDefinition()
    {
        $id = $this->getServiceId('manager');
        if (!$this->container->has($id)) {
            $this->container->setAlias($id, new DI\Alias($this->getManagerServiceId()));
        }
    }

    /**
     * Creates the Repository service definition.
     */
    private function createRepositoryDefinition()
    {
        $id = $this->getServiceId('repository');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition($class = $this->getServiceClass('repository'));
            $definition->setArguments([
                new DI\Reference($this->getServiceId('manager')),
                new DI\Reference($this->getServiceId('metadata'))
            ]);
            if (is_array($this->options['translation'])) {
                $definition
                    ->addMethodCall('setLocaleProvider', [new DI\Reference('ekyna_core.locale_provider.request')]) // TODO alias / configurable ?
                    ->addMethodCall('setTranslatableFields', [$this->options['translation']['fields']])
                ;
            }
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the operator service definition.
     *
     * @TODO Swap with ResourceManager when ready.
     */
    private function createOperatorDefinition()
    {
        $id = $this->getServiceId('operator');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition($this->getServiceClass('operator'));
            $definition->setArguments([
                new DI\Reference($this->getManagerServiceId()),
                new DI\Reference($this->getEventDispatcherServiceId()),
                new DI\Reference($this->getServiceId('configuration')),
                $this->container->getParameter('kernel.debug')
            ]);
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Controller service definition.
     */
    private function createControllerDefinition()
    {
        $id = $this->getServiceId('controller');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition($this->getServiceClass('controller'));
            $definition
                ->addMethodCall('setConfiguration', [new DI\Reference($this->getServiceId('configuration'))])
                ->addMethodCall('setContainer', [new DI\Reference('service_container')])
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Form service definition.
     */
    private function createFormDefinition()
    {
        if (null === $this->options['form']) {
            return;
        }

        $id = $this->getServiceId('form_type');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition($this->getServiceClass('form'));
            $definition
                ->setArguments([$this->options['entity']])
                ->addTag('form.type')
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Table service definition.
     */
    private function createTableDefinition()
    {
        if (null === $this->options['table']) {
            return;
        }

        $id = $this->getServiceId('table_type');
        if (!$this->container->has($id)) {
            $definition = new DI\Definition($this->getServiceClass('table'));
            $definition
                ->setArguments([$this->options['entity']])
                ->addTag('table.type', [
                    'alias' => sprintf('%s_%s', $this->namespace, $this->resourceId)]
                )
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Configure the translation
     */
    private function configureTranslations()
    {
        if (null !== array_key_exists('translation', $this->options) && is_array($this->options['translation'])) {
            $translatable = $this->options['entity'];
            $translation = $this->options['translation']['entity'];

            $id = sprintf('%s.%s_translation', $this->namespace, $this->resourceId);

            // Load metadata event mapping
            $mapping = [
                $translatable => $translation,
                $translation  => $translatable,
            ];
            if ($this->container->hasParameter('ekyna_resource.translation_mapping')) {
                $mapping = array_merge($this->container->getParameter('ekyna_resource.translation_mapping'), $mapping);
            }
            $this->container->setParameter('ekyna_resource.translation_mapping', $mapping);

            // Translation class parameter
            if (!$this->container->hasParameter($id.'.class')) {
                $this->container->setParameter($id.'.class', $translation);
            }

            // Inheritance mapping
            $this->configureInheritanceMapping($id, $translation, $this->options['translation']['repository']);
        }
    }

    /**
     * Configures mapping inheritance.
     *
     * @param string $id
     * @param string $entity
     * @param string $repository
     */
    private function configureInheritanceMapping($id, $entity, $repository)
    {
        $entities = [
            $id => [
                'class'      => $entity,
                'repository' => $repository,
            ],
        ];

        if ($this->container->hasParameter('ekyna_core.entities')) {
            $entities = array_merge($this->container->getParameter('ekyna_core.entities'), $entities);
        }
        $this->container->setParameter('ekyna_core.entities', $entities);
    }

    /**
     * Returns the default entity manager service id.
     *
     * @return string
     */
    private function getManagerServiceId()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Returns the event dispatcher service id.
     *
     * @return string
     */
    private function getEventDispatcherServiceId()
    {
        return 'event_dispatcher';
    }

    /**
     * Returns the service id for the given name.
     *
     * @param string $name
     *
     * @return string
     */
    private function getServiceId($name)
    {
        return sprintf('%s.%s.%s', $this->namespace, $this->resourceId, $name);
    }

    /**
     * Returns the service class for the given name.
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    private function getServiceClass($name)
    {
        $serviceId = $this->getServiceId($name);
        $parameterId = $serviceId.'.class';
        if ($this->container->hasParameter($parameterId)) {
            $class = $this->container->getParameter($parameterId);
        } elseif (array_key_exists($name, $this->options)) {
            $class = $this->options[$name];
        } else {
            throw new \RuntimeException(sprintf('Undefined "%s" service class.', $name));
        }
        return $class;
    }
}
