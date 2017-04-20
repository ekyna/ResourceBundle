<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Uploader;

use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\UploadableInterface;
use Psr\Container\ContainerInterface;

use function get_class;
use function sprintf;

/**
 * Class UploaderResolver
 * @package Ekyna\Bundle\ResourceBundle\Service\Uploader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploaderResolver
{
    private ContainerInterface $container;
    private array $resources = [];


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function register(string $resource, string $uploaderId): void
    {
        if (isset($this->resources[$resource])) {
            throw new RuntimeException(
                sprintf("Resource '%s' is already mapped to uploader '%s'.", $resource, $this->resources[$resource])
            );
        }

        $this->resources[$resource] = $uploaderId;
    }

    public function resolve(UploadableInterface $uploadable): UploaderInterface
    {
        $class = get_class($uploadable);

        if (!isset($this->resources[$class])) {
            throw new RuntimeException(sprintf("Resource '%s' is not registered.", $class));
        }

        $uploaderId = $this->resources[$class];

        if (!$this->container->has($uploaderId)) {
            throw new RuntimeException(sprintf("Uploader '%s' is not registered.", $uploaderId));
        }

        return $this->container->get($this->resources[$class]);
    }
}
