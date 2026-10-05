<?php
namespace App\Controllers;

use Crystal\Components\View;
use App\Databases\MainDb;
use App\Services\AuthService;

class IndexController
{
    /**
     * @param MainDb $mainDb
     */
    public function __construct(private MainDb $mainDb, private AuthService $auth)
    {
    }

    public function dashboard()
    {
        $this->auth->requireLogin();
        return View::render('dashboard', ['pageId' => 'dashboard']);
    }

    public function imageLibrary()
    {
        return View::render('image-library', ['pageId' => 'image-library']);
    }


    public function makeThumbnails(){

        $sourceDir = __DIR__ . '/../public/Uploads';
        $thumbDir  = __DIR__ . '/../public/Uploads/thumbs';

        $width  = 200;
        $height = 200;
        $quality = 40;

        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        foreach (glob($sourceDir . '/*.{jpg,jpeg,JPG,JPEG}', GLOB_BRACE) as $file) {
            $image = imagecreatefromjpeg($file);

            if (!$image) {
                echo "Failed: $file\n";
                continue;
            }

            $srcWidth  = imagesx($image);
            $srcHeight = imagesy($image);

            // Keep aspect ratio, fit inside 200x200
            $scale = min($width / $srcWidth, $height / $srcHeight);

            $newWidth  = max(1, (int)($srcWidth * $scale));
            $newHeight = max(1, (int)($srcHeight * $scale));

            $thumb = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresampled(
                $thumb,
                $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $srcWidth, $srcHeight
            );

            $output = $thumbDir . '/' .
                      pathinfo($file, PATHINFO_FILENAME) . '.avif';

            imageavif($thumb, $output, $quality);

            imagedestroy($image);
            imagedestroy($thumb);

            echo "Created: $output\n";
        }

    }
}
