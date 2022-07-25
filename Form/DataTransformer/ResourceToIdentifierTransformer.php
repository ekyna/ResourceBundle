<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Form\DataTransformer;

use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use function is_array;
use function is_iterable;
use function sprintf;

/**
 * Class ResourceToIdentifierTransformer
 * @package Ekyna\Bundle\ResourceBundle\Form\DataTransfomer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceToIdentifierTransformer implements DataTransformerInterface
{
    protected ?ResourceRepositoryInterface $repository;
    protected string                       $identifier;
    protected bool                         $multiple;

    public function __construct(
        ResourceRepositoryInterface $repository = null,
        string                      $identifier = 'id',
        bool                        $multiple = false
    ) {
        $this->repository = $repository;
        $this->identifier = $identifier;
        $this->multiple = $multiple;
    }

    public function setRepository(ResourceRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        $this->assertRepository();

        if (null === $value) {
            return $this->multiple ? [] : '';
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $class = $this->repository->getClassName();

        $transformer = function (ResourceInterface $entity) use ($class, $accessor): string {
            if (!$entity instanceof $class) {
                throw new UnexpectedTypeException($entity, $class);
            }

            $identifier = $accessor->getValue($entity, $this->identifier);

            if (empty($identifier)) {
                throw new TransformationFailedException(sprintf(
                    'Object "%s" identifier "%s" is empty.',
                    $class,
                    $this->identifier
                ));
            }

            return (string)$accessor->getValue($entity, $this->identifier);
        };

        if ($this->multiple) {
            $transformed = [];

            if (!is_iterable($value)) {
                throw new UnexpectedTypeException($value, 'iterable');
            }

            foreach ($value as $entity) {
                $transformed[] = $transformer($entity);
            }

            return $transformed;
        }

        return $transformer($value);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        $this->assertRepository();

        if (empty($value)) {
            return $this->multiple ? [] : null;
        }

        $transformer = function ($identifier): ResourceInterface {
            if ('id' === $this->identifier) {
                $entity = $this->repository->find((int)$identifier);
            } else {
                $entity = $this->repository->findOneBy([$this->identifier => $identifier]);
            }

            if (null === $entity) {
                throw new TransformationFailedException(sprintf(
                    'Object "%s" with identifier "%s"="%s" does not exist.',
                    $this->repository->getClassName(),
                    $this->identifier,
                    $identifier
                ));
            }

            return $entity;
        };

        if ($this->multiple) {
            $transformed = [];

            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }

            foreach ($value as $identifier) {
                $transformed[] = $transformer($identifier);
            }

            return $transformed;
        }

        return $transformer($value);
    }

    private function assertRepository(): void
    {
        if (null !== $this->repository) {
            return;
        }

        throw new LogicException('Repository is not set.');
    }
}
