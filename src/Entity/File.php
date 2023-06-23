<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Action\FileUploadAction;
use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            controller: FileUploadAction::class,
            openapiContext: [
                'summary' => 'Upload Accounting file form-data',
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'Choose a img or pdf file'
                                    ],
                                    'alias' => [
                                        'type' => 'string',
                                        'example' => 'My filename',
                                        'description' => 'Custom filename'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            deserialize: false
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['file:read'], 'openapi_definition_name' => 'Details'],
    denormalizationContext: ['groups' => ['file:write', 'openapi_definition_name' => 'Details']],
    order: ['createdAt' => 'DESC'],
    paginationClientEnabled: false,
    paginationItemsPerPage: 30,
    paginationMaximumItemsPerPage: 50
)]
class File
{
    const DIRNAME_FILES = 'files';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[ApiProperty(identifier: true)]
    #[Groups(['file:read'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['file:read', 'file:write'])]
    private ?string $alias = null;

    #[ORM\Column(length: 255)]
    #[Groups(['file:read'])]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['file:read'])]
    private ?string $thumbnail = null;

    #[ORM\Column(length: 255)]
    #[Groups(['file:read'])]
    private ?string $extension = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }
}
