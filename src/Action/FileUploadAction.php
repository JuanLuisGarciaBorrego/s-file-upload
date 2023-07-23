<?php
declare(strict_types=1);

namespace App\Action;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use App\Dto\FileMakerDto;
use App\Entity\File;
use App\Helper\UploadHelper;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class FileUploadAction
{
    /**
     * @throws FilesystemException
     */
    public function __invoke(Request $request, ValidatorInterface $validator, UploadHelper $uploadHelper): ?File
    {
        $fileDto = new FileMakerDto(
            $request->files->get('file'),
            $request->request->get('alias')
        );

        $violations = $validator->validate($fileDto);

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        $uploadFile = $uploadHelper->upload($fileDto->file, File::DIRNAME_FILES);

        $json = $this->transform(
            '/var/lib/libvips/exec_libvips.sock',
            $uploadHelper->getAbsolutePath($uploadFile['filename'], File::DIRNAME_FILES),
            $uploadHelper->getAbsolutePath('converterd_' . $uploadFile['filename'], File::DIRNAME_FILES),
            0,
            0,
            0,
            0,
            0,
            0
        );

        $fileEntity = new File();
        $fileEntity->setUuid((string)Uuid::v4());
        $fileEntity->setAlias($fileDto->alias ?? $fileDto->file->getClientOriginalName());
        $fileEntity->setFilename($uploadFile['filename']);
        $fileEntity->setExtension($uploadFile['extension']);

        return $fileEntity;
    }

    private function transform(string $socketLocation, string $inputFile, string $outputFile,
                               int    $cyan, int $magenta, int $yellow, int $black, int $blackType,
                               int    $colorType): array
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new Exception(socket_last_error());
        }
        if(socket_connect($socket, $socketLocation) === false) {
            throw new Exception("Unable to connect to specified socket location $socketLocation");
        }
        $json = json_encode([
            "input_file" => $inputFile,
            "output_file" => $outputFile,
            "cyan" => $cyan,
            "magenta" => $magenta,
            "black" => $black,
            "yellow" => $yellow,
            "colorType" => $colorType,
            "blackType" => $blackType,
        ]);
        $json_len = strlen($json);
        socket_write($socket, pack("Q", $json_len), 8);
        socket_write($socket, $json, $json_len);
        $json_len = "";
        socket_recv($socket, $json_len, 8, MSG_WAITALL);
        $json_len = unpack("Q", $json_len)[1];
        $json = "";
        socket_recv($socket, $json, $json_len, MSG_WAITALL);
        $json = json_decode($json, true);
            
        return $json;
    }
}
