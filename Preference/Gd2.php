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
     * @param $newWidth
     * @param $newHeight
     *
     * @return void
     */
    public function resizeToSquare($newWidth, $newHeight)
    {
        $origWidth  = $this->_imageSrcWidth;
        $origHeight = $this->_imageSrcHeight;

        $newRatio  = $newWidth / $newHeight;
        $origRatio = $origWidth / $origHeight;

        if ($origRatio > $newRatio) {
            if ($origHeight < $newHeight) {
                $newHeight = $origHeight;
            }
            $tempWidth = round($origRatio * $newHeight);

            $this->resize($tempWidth, $newHeight);

            $cropAmount    = floor(($tempWidth - $newWidth) / 2);
            $cropRemainder = ($tempWidth - $newWidth) % 2;

            $this->crop(0, $cropAmount + $cropRemainder, $cropAmount, 0);
        } else {
            if ($origWidth < $newWidth) {
                $newWidth = $origWidth;
            }

            $tempHeight = round((1 / $origRatio) * $newWidth);

            $this->resize($newWidth, $tempHeight);

            $cropAmount    = floor(($tempHeight - $newHeight) / 2);
            $cropRemainder = ($tempHeight - $newHeight) % 2;

            $this->crop($cropAmount + $cropRemainder, 0, 0, $cropAmount);
        }
    }
}
