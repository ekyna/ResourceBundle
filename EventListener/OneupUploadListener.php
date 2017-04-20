<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\EventListener;

use Oneup\UploaderBundle\Event\PostUploadEvent;
use Oneup\UploaderBundle\UploadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class OneupUploadListener
 * @package Ekyna\Bundle\CoreBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OneupUploadListener implements EventSubscriberInterface
{
    /**
     * Post upload event handler (returns the upload key).
     *
     * @param PostUploadEvent $event
     */
    public function onPostUpload(PostUploadEvent $event): void
    {
        $response = $event->getResponse();

        $response['upload_key'] = null;

        $file = $event->getFile();
        if ($file instanceof File) {
            $response['upload_key'] = $file->getFilename();

            // TODO check if tmp filesystem has key
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UploadEvents::POST_UPLOAD => ['onPostUpload'],
        ];
    }
}
