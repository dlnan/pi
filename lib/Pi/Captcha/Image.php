<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Captcha;

use Laminas\Captcha\Exception;
use Laminas\Captcha\Image as LaminasImage;
use Laminas\Stdlib\ErrorException;
use Laminas\Stdlib\ErrorHandler;

/**
 * CAPTCHA image class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Image extends LaminasImage
{
    /**
     * {@inheritDoc}
     */
    public function setImgUrl($imgUrl)
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * Create image for the CAPTCHA
     *
     * @param string $id
     * @param bool   $refresh
     *
     * @return resource
     */
    public function createImage($id, $refresh = true)
    {
        $this->setId($id);
        if ($refresh) {
            $word = $this->generateWord();
            $this->setWord($word);
        }
        $word  = $this->getWord();
        $image = $this->generateImage($id, $word);

        return $image;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        if (!$this->keepSession) {
            $this->session = null;
        }
        $id = $this->generateRandomId();
        $this->setId($id);
        $word = $this->generateWord();
        $this->setWord($word);

        return $id;
    }

    /**
     * {@inheritDoc}
     *
     * @return resource
     */
    protected function generateImage($id, $word)
    {
        $font = $this->getFont();

        if (empty($font)) {
            throw new Exception\NoFontProvidedException(
                'Image CAPTCHA requires font'
            );
        }

        $w     = $this->getWidth();
        $h     = $this->getHeight();
        $fsize = $this->getFontSize();

        //$img_file   = $this->getImgDir() . $id . $this->getSuffix();

        if (empty($this->startImage)) {
            $img = imagecreatetruecolor($w, $h);
        } else {
            // Potential error is change to exception
            ErrorHandler::start();
            $img   = imagecreatefrompng($this->startImage);
            $error = ErrorHandler::stop();
            if (!$img || $error instanceof ErrorException) {
                throw new Exception\ImageNotLoadableException(
                    'Can not load start image'
                );
            }
            $w = imagesx($img);
            $h = imagesy($img);
        }

        $text_color = imagecolorallocate($img, 0, 0, 0);
        $bg_color   = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bg_color);
        $textbox = imageftbbox($fsize, 0, $font, $word);
        $x       = ($w - ($textbox[2] - $textbox[0])) / 2;
        $y       = ($h - ($textbox[7] - $textbox[1])) / 2;
        imagefttext($img, $fsize, 0, $x, $y, $text_color, $font, $word);

        // generate noise
        for ($i = 0; $i < $this->dotNoiseLevel; $i++) {
            imagefilledellipse(
                $img,
                mt_rand(0, $w),
                mt_rand(0, $h),
                2,
                2,
                $text_color
            );
        }
        for ($i = 0; $i < $this->lineNoiseLevel; $i++) {
            imageline(
                $img,
                mt_rand(0, $w),
                mt_rand(0, $h),
                mt_rand(0, $w),
                mt_rand(0, $h),
                $text_color
            );
        }

        // transformed image
        $img2     = imagecreatetruecolor($w, $h);
        $bg_color = imagecolorallocate($img2, 255, 255, 255);
        imagefilledrectangle($img2, 0, 0, $w - 1, $h - 1, $bg_color);

        // apply wave transforms
        $freq1 = $this->randomFreq();
        $freq2 = $this->randomFreq();
        $freq3 = $this->randomFreq();
        $freq4 = $this->randomFreq();

        $ph1 = $this->randomPhase();
        $ph2 = $this->randomPhase();
        $ph3 = $this->randomPhase();
        $ph4 = $this->randomPhase();

        $szx = $this->randomSize();
        $szy = $this->randomSize();

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $sx = $x + (sin($x * $freq1 + $ph1)
                        + sin($y * $freq3 + $ph3)) * $szx;
                $sy = $y + (sin($x * $freq2 + $ph2)
                        + sin($y * $freq4 + $ph4)) * $szy;

                if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1) {
                    continue;
                } else {
                    $color    = (imagecolorat($img, $sx, $sy) >> 16)
                        & 0xFF;
                    $color_x  = (imagecolorat($img, $sx + 1, $sy) >> 16)
                        & 0xFF;
                    $color_y  = (imagecolorat($img, $sx, $sy + 1) >> 16)
                        & 0xFF;
                    $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16)
                        & 0xFF;
                }

                if ($color == 255 && $color_x == 255 && $color_y == 255
                    && $color_xy == 255
                ) {
                    // ignore background
                    continue;
                } elseif ($color == 0 && $color_x == 0 && $color_y == 0
                    && $color_xy == 0
                ) {
                    // transfer inside of the image as-is
                    $newcolor = 0;
                } else {
                    // do antialiasing for border items
                    $frac_x  = $sx - floor($sx);
                    $frac_y  = $sy - floor($sy);
                    $frac_x1 = 1 - $frac_x;
                    $frac_y1 = 1 - $frac_y;

                    $newcolor = $color * $frac_x1 * $frac_y1
                        + $color_x * $frac_x * $frac_y1
                        + $color_y * $frac_x1 * $frac_y
                        + $color_xy * $frac_x * $frac_y;
                }

                imagesetpixel(
                    $img2,
                    $x,
                    $y,
                    imagecolorallocate(
                        $img2,
                        $newcolor,
                        $newcolor,
                        $newcolor
                    )
                );
            }
        }

        // generate noise
        for ($i = 0; $i < $this->dotNoiseLevel; $i++) {
            imagefilledellipse(
                $img2,
                mt_rand(0, $w),
                mt_rand(0, $h),
                2,
                2,
                $text_color
            );
        }

        for ($i = 0; $i < $this->lineNoiseLevel; $i++) {
            imageline(
                $img2,
                mt_rand(0, $w),
                mt_rand(0, $h),
                mt_rand(0, $w),
                mt_rand(0, $h),
                $text_color
            );
        }

        /*
        imagepng($img2, $img_file);
        imagedestroy($img);
        imagedestroy($img2);
        */

        imagedestroy($img);

        return $img2;
    }
}
