<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Symfony\Component\Serializer\Serializer;

/**
 * Trait SerializerTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait SerializerTrait
{
    private Serializer $serializer;

    /**
     * @required
     */
    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    protected function getSerializer(): Serializer
    {
        return $this->serializer;
    }
}
