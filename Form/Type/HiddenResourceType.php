<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HiddenResourceType
 * @package Ekyna\Bundle\ResourceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class HiddenResourceType extends AbstractType
{
    private RepositoryFactoryInterface $factory;
    private DoctrineOrmTypeGuesser $guesser;

    public function __construct(RepositoryFactoryInterface $factory, DoctrineOrmTypeGuesser $guesser)
    {
        $this->factory = $factory;
        $this->guesser = $guesser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new ResourceToIdentifierTransformer();
        $builder->addViewTransformer($transformer);

        if (empty($options['class'])) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer) {
                $form = $event->getForm();
                $class = $form->getParent()->getConfig()->getDataClass();
                $property = $form->getName();
                if (null === $guessedType = $this->guesser->guessType($class, $property)) {
                    throw new \RuntimeException(sprintf('Unable to guess the type for "%s" property.', $property));
                }
                $typeOptions = $guessedType->getOptions();
                $repository = $this->factory->getRepository($typeOptions['class']);
                $transformer->setRepository($repository);
            });
        } else {
            $repository = $this->factory->getRepository($options['class']);
            $transformer->setRepository($repository);
        }

        $transformer->setIdentifier($options['identifier']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class'      => null,
                'identifier' => 'id',
            ])
            ->setAllowedTypes('class', ['null', 'string'])
            ->setAllowedTypes('identifier', 'string');
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
