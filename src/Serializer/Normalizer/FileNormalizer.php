<?php
declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\File;
use App\Helper\UploadHelper;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FileNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'FILE_NORMALIZER_ALREADY_CALLED';

    public function __construct(readonly private UploadHelper $uploadHelper)
    {
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var File $object */
        $filenameAbsolute = $this->uploadHelper->getPublicPath($object->getFilename(), File::DIRNAME_FILES);
        $object->setFilename($filenameAbsolute);

        if($object->getThumbnail()) {
            $thumbnailAbsolute = $this->uploadHelper->getPublicPath($object->getThumbnail(), File::DIRNAME_FILES);
            $object->setThumbnail($thumbnailAbsolute);
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof File;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            File::class,
        ];
    }
}