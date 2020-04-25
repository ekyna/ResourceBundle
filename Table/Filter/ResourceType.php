<?php

namespace Ekyna\Bundle\ResourceBundle\Table\Filter;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\ResourceBundle\Table\Filter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractFilterType
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $registry
     */
    public function __construct(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('resource')
            ->setDefault('class', function(Options $options, $value) {
                $class = $this
                    ->registry
                    ->findById($options['resource'])
                    ->getResourceClass();

                if ($value && $value !== $class) {
                    throw new InvalidOptionsException("Options 'resource' and 'class' miss match.");
                }

                return $class;
            })
            ->setDefault('label', function(Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $this
                    ->registry
                    ->findById($options['resource'])
                    ->getResourceLabel();
            });
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
