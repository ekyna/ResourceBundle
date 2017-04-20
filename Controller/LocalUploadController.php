<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Controller;

use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LocalUploadController
 * @package Ekyna\Bundle\ResourceBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LocalUploadController
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function download(Request $request): Response
    {
        $helper = new FilesystemHelper($this->filesystem);

        if (empty($key = $request->attributes->get('key'))) {
            throw new NotFoundHttpException('File not found');
        }

        if (!$helper->fileExists($key, false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $helper->createFileResponse($key);
    }
}
