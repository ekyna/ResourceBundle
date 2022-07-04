<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\EventListener;

use DateTime;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploaderResolver;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploadToggler;
use Ekyna\Component\Resource\Model\UploadableInterface;

/**
 * Class UploadableListener
 * @package Ekyna\Bundle\ResourceBundle\Listener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploadableListener
{
    private UploaderResolver $resolver;
    private UploadToggler $toggler;


    public function __construct(UploaderResolver $resolver, UploadToggler $toggler)
    {
        $this->resolver = $resolver;
        $this->toggler = $toggler;
    }

    /**
     * Pre persist event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function prePersist(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        // TODO Remove (when handled by timestampable resource behavior).
        $uploadable
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $this->resolver->resolve($uploadable)->prepare($uploadable);
    }

    /**
     * Post persist event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postPersist(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        $this->resolver->resolve($uploadable)->upload($uploadable);
    }

    /**
     * Pre update event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function preUpdate(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        // TODO Remove (when handled by resource behavior).
        $uploadable->setUpdatedAt(new DateTime());

        $this->resolver->resolve($uploadable)->prepare($uploadable);
    }

    /**
     * Post update event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postUpdate(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        $this->resolver->resolve($uploadable)->upload($uploadable);
    }

    /**
     * Pre remove event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function preRemove(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        $uploadable->setOldPath($uploadable->getPath());
    }

    /**
     * Post remove event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postRemove(UploadableInterface $uploadable): void
    {
        if (!$this->toggler->isEnabled()) {
            return;
        }

        $this->resolver->resolve($uploadable)->remove($uploadable);
    }
}
