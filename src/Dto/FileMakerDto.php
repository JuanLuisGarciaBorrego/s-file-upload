<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class FileMakerDto
{
    #[Assert\NotBlank]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: ['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/png', 'image/gif']
    )]
    public UploadedFile $file;

    public ?string $alias;

    public function __construct(UploadedFile $file, ?string $alias)
    {
        $this->file = $file;
        $this->alias = $alias;
    }
}