<?php

/**
 * E2pdf Signature Model
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.00.10
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_Signature extends Model_E2pdf_Model {

    public function ttf_signature($value, $size, $font, $options) {

        $box = imagettfbbox($size, 0, $font, $value);

        $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
        $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
        $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
        $max_y = max(array($box[1], $box[3], $box[5], $box[7]));

        $box = array(
            'x' => abs($min_x),
            'y' => abs($min_y),
            'width' => $max_x - $min_x,
            'height' => $max_y - $min_y
        );

        $box = $this->ttf_box_fix($value, $box, $size, $font);

        if ($box['width'] > 0 && $box['height'] > 0) {
            $img = imagecreatetruecolor($box['width'], $box['height']);
            if ($options['bgColour'] == 'transparent') {
                imagealphablending($img, false);
                imagesavealpha($img, true);
                $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
            } else {
                $bg = imagecolorallocate($img, $options['bgColour'][0], $options['bgColour'][1], $options['bgColour'][2]);
            }

            $pen = imagecolorallocate($img, $options['penColour'][0], $options['penColour'][1], $options['penColour'][2]);
            imagefill($img, 0, 0, $bg);
            imagettftext($img, $size, 0, $box['x'], $box['y'], $pen, $font, $value);
            ob_start();
            imagepng($img);
            $tmp_image = ob_get_contents();
            ob_end_clean();
            $value = base64_encode($tmp_image);
        } else {
            $value = '';
        }

        return $value;
    }

    /*
     * https://github.com/unlocomqx/text-measure
     */

    public function ttf_box_fix($value, $box, $size, $font) {

        $img = $this->ttf_tmp($value, $box, $size, $font);

        //top add
        $top_line = true;
        while ($top_line) {
            $found = false;
            $y = 1;
            for ($x = 0; $x < $box['width']; $x++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $top_line = false;
                break;
            } else {
                $box['y'] ++;
                $box['height'] ++;
                imagedestroy($img);
                $img = $this->ttf_tmp($value, $box, $size, $font);
            }
        }


        //bottom add
        $bottom_line = true;
        while ($bottom_line) {
            $found = false;
            $y = $box['height'] - 1;
            for ($x = 0; $x < $box['width']; $x++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $bottom_line = false;
                break;
            } else {
                $box['height'] ++;
                imagedestroy($img);
                $img = $this->ttf_tmp($value, $box, $size, $font);
            }
        }

        //left add
        $left_line = true;
        while ($left_line) {
            $found = false;
            $x = 1;
            for ($y = 0; $y < $box['height']; $y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $left_line = false;
                break;
            } else {
                $box['x'] ++;
                $box['width'] ++;
                imagedestroy($img);
                $img = $this->ttf_tmp($value, $box, $size, $font);
            }
        }

        //right add
        $right_line = true;
        while ($right_line) {
            $found = false;
            $x = $box['width'] - 1;
            for ($y = 0; $y < $box['height']; $y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $right_line = false;
                break;
            } else {
                $box['width'] ++;
                imagedestroy($img);
                $img = $this->ttf_tmp($value, $box, $size, $font);
            }
        }

        //top trim
        $found = false;
        for ($y = 0; $y < $box['height']; $y++) {
            for ($x = 0; $x < $box['width']; $x++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $box['y'] --;
                $box['height'] --;
            } else {
                break;
            }
        }


        //bottom trim
        $found = false;
        for ($y = $box['height'] - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $box['width']; $x++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $box['height'] --;
            } else {
                break;
            }
        }



        //left trim
        $found = false;
        for ($x = 0; $x < $box['width']; $x++) {
            for ($y = 0; $y < $box['height']; $y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $box['x'] --;
                $box['width'] --;
            } else {
                break;
            }
        }


        //right trim
        $found = false;
        for ($x = $box['width'] - 1; $x >= 0; $x--) {
            for ($y = 0; $y < $box['height']; $y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $box['width'] --;
            } else {
                break;
            }
        }
        return $box;
    }

    public function ttf_tmp($value, $box, $size, $font) {
        $img = imagecreatetruecolor($box['width'], $box['height']);
        $black = imagecolorallocate($img, 0, 0, 0);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $black);
        imagettftext($img, $size, 0, (int) $box['x'], $box['y'], $red, $font, $value);
        imagecolordeallocate($img, $black);
        imagecolordeallocate($img, $red);
        return $img;
    }

}
