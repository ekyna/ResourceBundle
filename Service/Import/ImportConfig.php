<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Import;

use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Import\ImportConfig as BaseConfig;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function pathinfo;
use function sys_get_temp_dir;
use function transliterator_transliterate;
use function uniqid;

use const PATHINFO_FILENAME;

/**
 * Class ImportConfig
 * @package Ekyna\Bundle\ResourceBundle\Service\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ImportConfig extends BaseConfig
{
    private ?UploadedFile $file = null;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): ImportConfig
    {
        $this->file = $file;

        $this->setPath(null);

        return $this;
    }

    public function getPath(): ?string
    {
        if (null !== $path = parent::getPath()) {
            return $path;
        }

        if (null === $this->file) {
            return null;
        }

        $originalFilename = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalFilename
        );
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $this->file->guessExtension();

        try {
            $file = $this->file->move(sys_get_temp_dir(), $newFilename);
        } catch (FileException $e) {
            throw new ImportException($e->getMessage());
        }

        $this->setPath($path = $file->getRealPath());

        return $path;
    }
}
