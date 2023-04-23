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

    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 300, ?int $height = 150)
    {
        // change the filename to a unique name
        $filename = md5(uniqid(rand(), true)) . '.webp';

        // get image info
        $picture_infos = getimagesize($picture);

        if ($picture_infos === false) {
            throw new \Exception('Format d\'image non supporté');
        }

        // check image format
        switch ($picture_infos['mime']) {
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($picture);
                break;
            case 'image/png':
                $picture_source = imagecreatefrompng($picture);
                break;
            case 'image/webp':
                $picture_source = imagecreatefromwebp($picture);
                break;
            default:
                throw new \Exception('Format d\'image non supporté');
        }

        // resize image

        // get dimensions
        $imageWidth = $picture_infos[0];
        $imageHeight = $picture_infos[1];

        // orientation
        switch ($imageWidth <=> $imageHeight) {
            case -1: // portrait
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeight - $squareSize) / 2;
                break;
            case 0: // carré
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1: // paysage
                $squareSize = $imageHeight;
                $src_x = ($imageWidth - $squareSize) / 2;
                $src_y = 0;
                break;
        }

        // create a new empty image
        $resized_picture = imagecreatetruecolor($width, $height);

        imagecopyresampled(
            $resized_picture,
            $picture_source,
            0,
            0,
            $src_x,
            $src_y,
            $width,
            $height,
            $squareSize,
            $squareSize
        );

        $path = $this->params->get('images_directory') . $folder;

        // create the folder if it doesn't exist
        if (!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/', 0755, true);
        }

        // save the image
        imagewebp($resized_picture, $path . '/mini/' . $width . 'x' . $height . '-' . $filename);

        $picture->move($path . '/', $filename);

        return $filename;
    }

    public function delete(string $file, ?string $folder = '', ?int $width = 300, ?int $height = 150)
    {
        if ($file !== 'default.webp') {
            $success_mini = false;
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $file;
            if (file_exists($mini)) {
                $success = unlink($mini);
                $success_mini = true;
            }

            $original = $path . '/' . $file;

            if (file_exists($original)) {
                $success = unlink($original);
                $success = true;
            }
            return $success_mini && $success; // todo : return true exception if one of the two is false
        }
        return false;
    }
}