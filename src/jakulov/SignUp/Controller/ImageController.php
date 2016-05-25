<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 26.05.16
 * Time: 0:02
 */

namespace jakulov\SignUp\Controller;

use jakulov\SignUp\Http\JsonResponse;
use jakulov\SignUp\Model\AuthToken;
use jakulov\SignUp\Service\Language;

/**
 * Class ImageController
 * @package jakulov\SignUp\Controller
 */
class ImageController extends Controller
{
    /**
     * @return JsonResponse
     */
    protected function uploadAction()
    {
        $allowFormats = ['jpg', 'gif', 'jpeg', 'png'];
        $data = ['ok' => 0, 'error' => 'No input file'];
        if($this->getRequest()->getMethod() === 'POST') {
            $file = isset($_FILES['file']) ? $_FILES['file'] : null;
            if($file) {
                if($file['error'] == 0) {
                    $tmpName = $file['tmp_name'];
                    $extension = explode('.', $file['name']);
                    $extension = mb_strtolower(end($extension));
                    if(in_array($extension, $allowFormats)) {
                        $newFile = AuthToken::generateRandomToken(20) .'.'. $extension;
                        $ok = move_uploaded_file($tmpName, UPLOAD_DIR .'/'. $newFile);
                        if($ok) {
                            $ok = $this->resizeFile(UPLOAD_DIR .'/'. $newFile, UPLOAD_DIR .'/r'. $newFile);
                        }
                        if($ok) {
                            unlink(UPLOAD_DIR .'/'. $newFile);
                            $data = [
                                'ok' => 1,
                                'file' => 'r'. $newFile,
                                'url' => UPLOAD_PATH .'/r'. $newFile
                            ];
                        }
                    }
                    else {
                        $data['error'] = Language::get(ALLOW_FILE_FORMAT) .': '. join(',', $allowFormats);
                    }
                }
                else {
                    $data['error'] = 'File upload error #'. $file['error'];
                }
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @param $path
     * @param $new_thumb_loc
     * @param int $new_width
     * @param int $new_height
     * @return bool
     */
    protected function resizeFile($path, $new_thumb_loc, $new_width = 250, $new_height = 250)
    {
        $mime = getimagesize($path);

        $srcImg = false;
        if ($mime['mime'] == 'image/png') {
            $srcImg = imagecreatefrompng($path);
        }
        if ($mime['mime'] == 'image/jpg') {
            $srcImg = imagecreatefromjpeg($path);
        }
        if ($mime['mime'] == 'image/jpeg') {
            $srcImg = imagecreatefromjpeg($path);
        }
        if ($mime['mime'] == 'image/gif') {
            $srcImg = imagecreatefromgif($path);
        }
        if ($mime['mime'] == 'image/pjpeg') {
            $srcImg = imagecreatefromjpeg($path);
        }

        if (!$srcImg) {
            return false;
        }

        $old_x = imageSX($srcImg);
        $old_y = imageSY($srcImg);

        if ($old_x > $old_y) {
            $thumbWidth = $new_width;
            $thumbHeight = $old_y * ($new_height / $old_x);
        } elseif ($old_x < $old_y) {
            $thumbWidth = $old_x * ($new_width / $old_y);
            $thumbHeight = $new_height;
        } else {
            $thumbWidth = $new_width;
            $thumbHeight = $new_height;
        }

        $dstImg = ImageCreateTrueColor($thumbWidth, $thumbHeight);

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $old_x, $old_y);


        $result = false;
        if ($mime['mime'] == 'image/png') {
            $result = imagepng($dstImg, $new_thumb_loc, 8);
        }
        if ($mime['mime'] == 'image/jpg') {
            $result = imagejpeg($dstImg, $new_thumb_loc, 80);
        }
        if ($mime['mime'] == 'image/jpeg') {
            $result = imagejpeg($dstImg, $new_thumb_loc, 80);
        }
        if ($mime['mime'] == 'image/gif') {
            $result = imagegif($dstImg, $new_thumb_loc);
        }
        if ($mime['mime'] == 'image/pjpeg') {
            $result = imagejpeg($dstImg, $new_thumb_loc, 80);
        }

        imagedestroy($dstImg);
        imagedestroy($srcImg);

        return $result;
    }
}