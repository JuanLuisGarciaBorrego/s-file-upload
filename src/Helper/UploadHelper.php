<?php
declare(strict_types=1);

namespace App\Helper;

use Exception;
use Jcupitt\Vips;
use JetBrains\PhpStorm\ArrayShape;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * Enabled ffi php.ini
 * ffi.enable = true
 */
class UploadHelper
{
    const THUMBNAIL_PREFIX = 'thumbnail-';

    public function __construct(
        readonly string             $uploadsPath,
        readonly string             $dirnameUpload,
        readonly FilesystemOperator $publicUploadsFilesystem,
        readonly private UrlHelper  $urlHelper
    )
    {
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    #[ArrayShape(['filename' => 'string', 'extension' => 'string'])]
    public function upload(?UploadedFile $file, ?string $dirname = null): array
    {
        $filename = uniqid() . '.' . $file->guessExtension();

        $filesystem = $this->publicUploadsFilesystem;

        $stream = fopen($file->getPathname(), 'r');

        $result = $filesystem->writeStream(
            $dirname ? $dirname . '/' . $filename : $filename,
            $stream
        );

        if ($result === false) {
            throw new Exception(sprintf("Could not write uploaded file '%s'", $file));
        }
        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'filename' => $filename,
            'extension' => $file->guessExtension()
        ];
    }

    public function createThumbnail($filename, ?string $dirname = null, int $width = 400): string
    {
        try {
            $image = Vips\Image::thumbnail($this->getAbsolutePath($filename, $dirname), $width);

            $image->writeToFile($this->getAbsolutePath(self::THUMBNAIL_PREFIX . $filename, $dirname));
        } catch (Exception $exception) {
            throw new FileException('Error create thumbnail: ' . $exception->getMessage());
        }

        return self::THUMBNAIL_PREFIX . $filename;
    }

    public function getPublicPath(string $filename, ?string $dirname = null): string
    {
        return $this->urlHelper->getAbsoluteUrl($this->getRelativePath($filename, $dirname));
    }

    /**
     * return upload/dirnameOptional/filename.extension
     */
    public function getRelativePath(string $filename, ?string $dirname = null): ?string
    {
        return $dirname ? $dirname . '/' . $filename : $filename;
    }

    /**
     * return absolutePath/upload/dirnameOptional/filename.extension
     */
    public function getAbsolutePath(string $filename, ?string $dirname = null): ?string
    {
        return $this->uploadsPath . '/' . $this->getRelativePath($filename, $dirname);
    }
}