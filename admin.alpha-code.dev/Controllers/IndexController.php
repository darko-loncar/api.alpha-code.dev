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

    public function dashboard(): callable
    {
        /**
        $this->auth->requireLogin();

        $to = 'nyxx@192.168.1.102';
        $subject = 'PHP mail test';
        $message = 'This is a test email sent using PHP mail().';

        $headers = [
            'From: webserver@your-local-domain',
            'Content-Type: text/plain; charset=UTF-8'
        ];

        $result = mail($to, $subject, $message, implode("\r\n", $headers));

        echo $result ? 'mail() accepted the message' : 'mail() failed';
        */

        return View::response('dashboard', ['pageId' => 'dashboard']);
    }

    public function imageLibrary(): callable
    {
        return View::response('image-library', ['pageId' => 'image-library']);
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
