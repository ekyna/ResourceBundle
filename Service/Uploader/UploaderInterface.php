<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Uploader;

use Ekyna\Component\Resource\Model\UploadableInterface;

/**
 * Interface UploaderInterface
 * @package Ekyna\Bundle\ResourceBundle\Uploader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface UploaderInterface
{
    /**
     * Prepare the entity for upload.
     *
     * @param UploadableInterface $uploadable
     */
    public function prepare(UploadableInterface $uploadable): void;

    /**
     * Move the uploadable file.
     *
     * @param UploadableInterface $uploadable
     */
    public function upload(UploadableInterface $uploadable): void;

    /**
     * Unlink the file.
     *
     * @param UploadableInterface $uploadable
     */
    public function remove(UploadableInterface $uploadable): void;
}
