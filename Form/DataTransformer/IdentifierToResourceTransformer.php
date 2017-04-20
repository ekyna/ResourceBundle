<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use function count;
use function implode;
use function is_array;
use function sprintf;

/**
 * Class IdentifierToResourceTransformer
 * @package Ekyna\Bundle\ResourceBundle\Form\DataTransformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdentifierToResourceTransformer implements DataTransformerInterface
{
    protected ?ResourceRepositoryInterface $repository;
    protected string                       $identifier;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface|null $repository
     * @param string                           $identifier
     */
    public function __construct(ResourceRepositoryInterface $repository = null, string $identifier = 'id')
    {
        $this->repository = $repository;
        $this->identifier = $identifier;
    }

    /**
     * Sets the repository.
     *
     * @param ResourceRepositoryInterface $repository
     */
    public function setRepository(ResourceRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Sets the identifier.
     *
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        $this->assertRepository();

        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            if (null === $entities = $this->repository->findBy([$this->identifier => $value])) {
                throw new TransformationFailedException(sprintf(
                    'Objects "%s" could not be converted from value "%" with identifier "%s".',
                    $this->repository->getClassName(),
                    implode(', ', $value),
                    $this->identifier
                ));
            } elseif (count($entities) !== count($value)) {
                throw new TransformationFailedException(sprintf(
                    'One or more objects "%s" could not be converted from value "%s" with identifier "%s".',
                    $this->repository->getClassName(),
                    implode(', ', $value),
                    $this->identifier
                ));
            } else {
                return $entities;
            }
        } elseif (null === $entity = $this->repository->findOneBy([$this->identifier => $value])) {
            throw new TransformationFailedException(sprintf(
                'Object "%s" with identifier "%s"="%s" does not exist.',
                $this->repository->getClassName(),
                $this->identifier,
                $value
            ));
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        $this->assertRepository();

        if (null === $value) {
            return null;
        }

        $class = $this->repository->getClassName();
        $accessor = PropertyAccess::createPropertyAccessor();

        if ($value instanceof ArrayCollection) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $identifiers = [];
            foreach ($value as $entity) {
                if (!$entity instanceof $class) {
                    throw new UnexpectedTypeException($entity, $class);
                }
                $identifiers[] = $accessor->getValue($entity, $this->identifier);
            }

            return $identifiers;
        } elseif (!$value instanceof $class) {
            throw new UnexpectedTypeException($value, $class);
        }

        return $accessor->getValue($value, $this->identifier);
    }

    /**
     * Asserts the the repository is set.
     */
    private function assertRepository(): void
    {
        if (null !== $this->repository) {
            return;
        }

        throw new LogicException('Repository is not set.');
    }
}
