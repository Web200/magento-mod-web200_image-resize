<?php

declare(strict_types=1);

namespace Web200\ImageResize\Preference;

/**
 * Class Gd2
 *
 * @package Web200\Preference
 */
class Gd2 extends \Magento\Framework\Image\Adapter\Gd2
{
    /**
     * @param $new_width
     * @param $new_height
     *
     * @return void
     */
    public function resizeToSquare($new_width, $new_height)
    {
        $origWidth  = $this->_imageSrcWidth;
        $origHeight = $this->_imageSrcHeight;

        $newRatio  = $new_width / $new_height;
        $origRatio = $origWidth / $origHeight;

        if ($origRatio > $newRatio) {
            $tempWidth = round($origRatio * $new_height);

            $this->resize($tempWidth, $new_height);

            $cropAmount    = floor(($tempWidth - $new_width) / 2);
            $cropRemainder = ($tempWidth - $new_width) % 2;

            $this->crop(0, $cropAmount + $cropRemainder, $cropAmount, 0);
        } else {
            $tempHeight = round((1 / $origRatio) * $new_width);

            $this->resize($new_width, $tempHeight);

            $cropAmount    = floor(($tempHeight - $new_height) / 2);
            $cropRemainder = ($tempHeight - $new_height) % 2;

            $this->crop($cropAmount + $cropRemainder, 0, 0, $cropAmount);
        }
    }
}
