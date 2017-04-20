<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Table\Filter;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\ResourceBundle\Table\Filter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractFilterType
{
    protected ResourceRegistryInterface $registry;


    public function __construct(ResourceRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('resource')
            ->setDefault('class', function (Options $options, $value) {
                $class = $this
                    ->registry
                    ->find($options['resource'])
                    ->getEntityClass();

                if ($value && $value !== $class) {
                    throw new InvalidOptionsException("Options 'resource' and 'class' miss match.");
                }

                return $class;
            })
            ->setDefault('label', function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                $config = $this->registry->find($options['resource']);

                return t($config->getResourceLabel(), [], $config->getTransDomain());
            });
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
