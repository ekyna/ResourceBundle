<?php

namespace Ekyna\Bundle\ResourceBundle\Configuration;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Bundle\AdminBundle\Controller;
use Ekyna\Component\Resource\Configuration\Configuration;
use Ekyna\Component\Resource\Doctrine\ORM;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
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
 * @todo    rename: This is much more than a "configuration builder"
 * @todo    move: In DI folder
 */
class ConfigurationBuilder
{
    const DEFAULT_CONTROLLER   = Controller\ResourceController::class;
    const CONTROLLER_INTERFACE = Controller\ResourceControllerInterface::class;

    const DEFAULT_OPERATOR   = ORM\Operator\ResourceOperator::class;
    const OPERATOR_INTERFACE = Operator\ResourceOperatorInterface::class;

    const DEFAULT_REPOSITORY   = ORM\ResourceRepository::class;
    const REPOSITORY_INTERFACE = ORM\ResourceRepositoryInterface::class;

    const TRANSLATABLE_DEFAULT_REPOSITORY   = ORM\TranslatableResourceRepository::class;
    const TRANSLATABLE_REPOSITORY_INTERFACE = ORM\TranslatableResourceRepositoryInterface::class;

    const FORM_INTERFACE  = FormTypeInterface::class;
    const TABLE_INTERFACE = TableTypeInterface::class;
    const EVENT_INTERFACE = ResourceEventInterface::class;

    const CONFIGURATION  = Configuration::class;
    const CLASS_METADATA = ClassMetadata::class;

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
     * @return ConfigurationBuilder
     * @throws \RuntimeException
     *
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
        // TODO normalizer (serialization) service
        // TODO (resource)event listener service

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
     * @noinspection PhpUnusedParameterInspection
     */
    private function getOptionsResolver()
    {
        // TODO Use/Merge ConfigurationFactory options resolver.
        if (null !== self::$optionsResolver) {
            return self::$optionsResolver;
        }

        $classExists = function ($class) {
            if (!class_exists($class)) {
                throw new InvalidOptionsException(sprintf('Class %s does not exists.', $class));
            }

            return true;
        };
        $classExistsAndImplements = function ($class, $interface) use ($classExists) {
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
        $validEvent = function ($value) use ($classExistsAndImplements) {
            if (null !== $value) {
                if (is_string($value)) {
                    return $classExistsAndImplements($value, self::EVENT_INTERFACE);
                } elseif (is_array($value) && isset($value['class'])) {
                    return $classExistsAndImplements($value['class'], self::EVENT_INTERFACE);
                }
            }

            return true;
        };

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'entity'       => null,
                'repository'   => null,
                'operator'     => self::DEFAULT_OPERATOR,
                'controller'   => self::DEFAULT_CONTROLLER,
                'templates'    => null,
                'form'         => null,
                'table'        => null,
                'event'        => null,
                'parent'       => null,
                'translation'  => null,
                'trans_prefix' => null,
            ])
            ->setAllowedTypes('entity', 'string')
            ->setAllowedTypes('repository', ['null', 'string'])
            ->setAllowedTypes('operator', 'string')
            ->setAllowedTypes('controller', 'string')
            ->setAllowedTypes('templates', ['null', 'string', 'array'])
            ->setAllowedTypes('form', ['null', 'string'])
            ->setAllowedTypes('table', ['null', 'string'])
            ->setAllowedTypes('event', ['null', 'string', 'array'])
            ->setAllowedTypes('parent', ['null', 'string'])
            ->setAllowedTypes('translation', ['null', 'array'])
            ->setAllowedTypes('trans_prefix', ['null', 'string'])
            ->setAllowedValues('entity', $classExists)
            ->setAllowedValues('operator', $validOperator)
            ->setAllowedValues('controller', $validController)
            ->setAllowedValues('form', $validForm)
            ->setAllowedValues('table', $validTable)
            ->setAllowedValues('event', $validEvent)
            ->setNormalizer('repository', function (Options $options, $value) use ($classExistsAndImplements) {
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
            // TODO event normalization ?
            // TODO templates normalization ?
        ;

        return self::$optionsResolver = $resolver;
    }

    /**
     * Creates the entity classes parameters.
     */
    private function createEntityClassParameter()
    {
        // Entity class parameter
        $id = $this->getServiceId('class');
        if (!$this->container->hasParameter($id)) {
            $this->container->setParameter($id, $this->options['entity']);
        }

        // Entity inheritance mapping
        $this->configureInheritanceMapping(
            $this->namespace . '.' . $this->resourceId,
            $this->options['entity'],
            $this->options['repository']
        );

        if (!$this->isTranslationConfigured()) {
            return;
        }

        // Translation class parameter
        $id = $this->getServiceId('class', true);
        if (!$this->container->hasParameter($id)) {
            $this->container->setParameter($id, $this->options['translation']['entity']);
        }

        // Translation inheritance mapping
        $this->configureInheritanceMapping(
            $this->namespace . '.' . $this->resourceId . '.translation',
            $this->options['translation']['entity'],
            $this->options['translation']['repository']
        );
    }

    /**
     * Creates the Configuration service definition.
     */
    private function createConfigurationDefinition()
    {
        $id = $this->getServiceId('configuration');
        if (!$this->container->has($id)) {

            $translation = null;
            if ($this->isTranslationConfigured()) {
                $translation = [
                    'entity' => $this->options['translation']['entity'],
                    'fields' => $this->options['translation']['fields'],
                ];
            }

            $config = [
                'namespace'    => $this->namespace,
                'id'           => $this->resourceId,
                'name'         => Inflector::camelize($this->resourceId),
                'parent_id'    => $this->options['parent'],
                'classes'      => [
                    'entity'     => $this->options['entity'],
                    'form_type'  => $this->getServiceClass('form'), // TODO
                    'table_type' => $this->getServiceClass('table'), // TODO
                ],
                'event'        => $this->options['event'],
                'templates'    => $this->options['templates'],
                'translation'  => $translation,
                'trans_prefix' => $this->options['trans_prefix'] ?: $this->namespace . '.' . $this->resourceId,
            ];

            $definition = new DI\Definition(self::CONFIGURATION);
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.configuration_factory'), 'createConfiguration'])
                ->setArguments([$config])
                ->addTag('ekyna_resource.configuration', [
                        'alias' => sprintf('%s_%s', $this->namespace, $this->resourceId),
                    ]
                );

            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the metadata service definition.
     */
    private function createMetadataDefinition()
    {
        $this->configureMetadata($this->getServiceId('metadata'), $this->getServiceId('class'));

        if ($this->isTranslationConfigured()) {
            $this->configureMetadata($this->getServiceId('metadata', true), $this->getServiceId('class', true));
        }
    }

    /**
     * Configures the metadata service.
     *
     * @param string $id
     * @param string $entityClass
     *
     * @return DI\Definition
     */
    private function configureMetadata(string $id, string $entityClass): DI\Definition
    {
        if ($this->container->has($id)) {
            return $this->container->getDefinition($id);
        }

        $definition = new DI\Definition(self::CLASS_METADATA);
        $definition
            ->setFactory([new DI\Reference($this->getManagerServiceId()), 'getClassMetadata'])
            ->setArguments([
                $this->container->getParameter($entityClass),
            ]);
        //->setPublic(false)

        $this->container->setDefinition($id, $definition);

        return $definition;
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
        $definition = $this->configureRepositoryDefinition(
            $this->getServiceId('repository'),
            $this->getServiceClass('repository'),
            $this->getServiceId('manager'),
            $this->getServiceId('metadata')
        );

        if (!$this->isTranslationConfigured()) {
            return;
        }

        if (!$definition->hasMethodCall('setLocaleProvider')) {
            $definition->addMethodCall('setLocaleProvider', [
                new DI\Reference('ekyna_resource.locale_provider') // TODO alias / configurable ?
            ]);
        }
        if (!$definition->hasMethodCall('setTranslatableFields')) {
            $definition->addMethodCall('setTranslatableFields', [
                $this->options['translation']['fields'],
            ]);
        }
        $this->configureRepositoryDefinition(
            $this->getServiceId('repository', true),
            $this->getServiceClass('repository', true),
            $this->getServiceId('manager'),
            $this->getServiceId('metadata', true)
        );
    }

    /**
     * Configures the repository service definition.
     *
     * @param string $id
     * @param string $repositoryClass
     * @param string $managerId
     * @param string $metadataId
     *
     * @return DI\Definition
     */
    private function configureRepositoryDefinition(
        string $id,
        string $repositoryClass,
        string $managerId,
        string $metadataId
    ): DI\Definition {
        if ($this->container->has($id)) {
            $definition = $this->container->getDefinition($id);

            // Change class if overridden
            if ($definition->getClass() != $repositoryClass) {
                $definition->setClass($repositoryClass);
            }

            if (!$definition->hasTag('doctrine.repository_service')) {
                $definition->addTag('doctrine.repository_service');
            }

            return $definition;
        }

        $definition = new DI\Definition($repositoryClass);
        $definition
            ->setArguments([
                new DI\Reference($managerId),
                new DI\Reference($metadataId),
            ])
            ->addTag('doctrine.repository_service');

        $this->container->setDefinition($id, $definition);

        return $definition;
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
                $this->container->getParameter('kernel.debug'),
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
                ->addMethodCall('setContainer', [new DI\Reference('service_container')]);

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
                ->addTag('form.type');

            $this->container->setDefinition($id, $definition);
        } else {
            // TODO Check service class
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
                ->addTag('table.type');

            $this->container->setDefinition($id, $definition);
        } else {
            // TODO Check service class
        }
    }

    /**
     * Configure the translation
     */
    private function configureTranslations()
    {
        if (!$this->isTranslationConfigured()) {
            return;
        }

        $translatable = $this->options['entity'];
        $translation = $this->options['translation']['entity'];

        // Load metadata event mapping
        $mapping = [
            $translatable => $translation,
            $translation  => $translatable,
        ];
        if ($this->container->hasParameter('ekyna_resource.translation_mapping')) {
            $mapping = array_merge($this->container->getParameter('ekyna_resource.translation_mapping'), $mapping);
        }
        $this->container->setParameter('ekyna_resource.translation_mapping', $mapping);
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

        if ($this->container->hasParameter('ekyna_resource.entities')) {
            $entities = array_merge($this->container->getParameter('ekyna_resource.entities'), $entities);
        }

        $this->container->setParameter('ekyna_resource.entities', $entities);
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
        return 'ekyna_resource.event_dispatcher';
    }

    /**
     * Returns the service id for the given name.
     *
     * @param string $name
     * @param bool   $translation
     *
     * @return string
     */
    private function getServiceId(string $name, bool $translation = false)
    {
        $format = '%s.%s.%s';

        if ($translation) {
            if (!$this->isTranslationConfigured()) {
                throw new RuntimeException("Translation is not defined");
            }
            $format = '%s.%s_translation.%s';
        }

        return sprintf($format, $this->namespace, $this->resourceId, $name);
    }

    /**
     * Returns the service class for the given name.
     *
     * @param string $name
     * @param bool   $translation
     *
     * @return string|null
     * @throws \RuntimeException
     *
     */
    private function getServiceClass(string $name, bool $translation = false)
    {
        $serviceId = $this->getServiceId($name, $translation);

        $parameterId = $serviceId . '.class';
        if ($this->container->hasParameter($parameterId)) {
            $class = $this->container->getParameter($parameterId);
        } elseif ($translation) {
            if (!$this->isTranslationConfigured()) {
                throw new \RuntimeException("Requested service class on a non translatable entity");
            }
            if (!isset($this->options['translation'][$name])) {
                throw new \RuntimeException(sprintf('Undefined translation "%s" service class.', $name));
            }
            $class = $this->options['translation'][$name];
        } elseif (array_key_exists($name, $this->options)) {
            $class = $this->options[$name];
        } else {
            throw new \RuntimeException(sprintf('Undefined "%s" service class.', $name));
        }

        return $class;
    }

    /**
     * Returns whether translation is configured for this resource.
     *
     * @return bool
     */
    private function isTranslationConfigured(): bool
    {
        return isset($this->options['translation']) && !empty($this->options['translation']);
    }
}
