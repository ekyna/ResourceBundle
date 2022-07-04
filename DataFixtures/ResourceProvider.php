<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DataFixtures;

use Decimal\Decimal;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Yaml\Yaml;

use function is_string;
use function sprintf;
use function str_replace;

/**
 * Class ResourceProvider
 * @package Ekyna\Bundle\ResourceBundle\DataFixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceProvider
{
    private RepositoryFactoryInterface $factory;

    public function __construct(RepositoryFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function decimal(string $value): Decimal
    {
        return new Decimal($value);
    }

    public function resource(string $resource, string $criteria): ResourceInterface
    {
        $parameters = Yaml::parse($criteria);

        foreach ($parameters as &$value) {
            if (!is_string($value)) {
                continue;
            }

            $value = str_replace('\\', '', $value);
        }

        $object = $this->factory->getRepository($resource)->findOneBy($parameters);

        if (!$object) {
            throw new RuntimeException(sprintf('Failed to fetch %s resource with %s criteria.', $resource, $criteria));
        }

        return $object;
    }
}
