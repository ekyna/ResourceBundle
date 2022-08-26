<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HiddenResourceType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class HiddenResourceType extends AbstractResourceChoiceType
{
    public function __construct(
        ResourceHelper                              $resourceHelper,
        private readonly RepositoryFactoryInterface $factory,
    ) {
        parent::__construct($resourceHelper);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new ResourceToIdentifierTransformer();
        $builder->addViewTransformer($transformer);

        $repository = $this->factory->getRepository($options['class']);
        $transformer->setRepository($repository);

        $transformer->setIdentifier($options['identifier']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('identifier', 'id')
            ->setAllowedTypes('identifier', 'string');

        $this->configureClassAndResourceOptions($resolver);
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
