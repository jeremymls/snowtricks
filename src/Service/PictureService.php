<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, ?string $folder = ''): string
    {
        // change the filename to a unique name
        $filename = md5(uniqid(rand(), true)) . '.webp';

        // get image info
        $picture_infos = getimagesize($picture);

        if ($picture_infos === false) {
            throw new \Exception('Format d\'image non supporté');
        }

        // check image format
        if (!in_array($picture_infos['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
            throw new \Exception('Format d\'image non supporté');
        }

        $path = $this->params->get('images_directory') . $folder;
        $picture->move($path . '/', $filename);

        return $filename;
    }

    public function delete(string $file, ?string $folder = ''): mixed
    {
        if ($file !== 'default.webp') {
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $original = $path . '/' . $file;

            if (file_exists($original)) {
                $success = unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}
