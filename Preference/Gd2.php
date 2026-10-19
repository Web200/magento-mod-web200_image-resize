<?php

declare(strict_types=1);

namespace Web200\ImageResize\Preference;

use Magento\Framework\Image\Adapter\Gd2 as MagentoGd2;

/**
 * Class Gd2
 *
 * @package Web200\ImageResize\Preference
 */
class Gd2 extends MagentoGd2
{
    /**
     * Resize image to square with crop
     *
     * @param int|float|null $newWidth
     * @param int|float|null $newHeight
     *
     * @return void
     */
    public function resizeToSquare($newWidth, $newHeight): void
    {
        $newWidth = (int)$newWidth;
        $newHeight = (int)$newHeight;

        $origWidth  = (int)$this->_imageSrcWidth;
        $origHeight = (int)$this->_imageSrcHeight;

        if ($newWidth <= 0 || $newHeight <= 0 || $origWidth <= 0 || $origHeight <= 0) {
            return;
        }

        $newRatio  = $newWidth / $newHeight;
        $origRatio = $origWidth / $origHeight;

        if ($origRatio > $newRatio) {
            if ($origHeight < $newHeight) {
                $newHeight = $origHeight;
            }
            $tempWidth = (int)round($origRatio * $newHeight);

            $this->resize($tempWidth, $newHeight);

            $cropAmount    = (int)floor(($tempWidth - $newWidth) / 2);
            $cropRemainder = (int)(($tempWidth - $newWidth) % 2);

            $this->crop(0, $cropAmount + $cropRemainder, $cropAmount, 0);
        } else {
            if ($origWidth < $newWidth) {
                $newWidth = $origWidth;
            }

            $tempHeight = (int)round((1 / $origRatio) * $newWidth);

            $this->resize($newWidth, $tempHeight);

            $cropAmount    = (int)floor(($tempHeight - $newHeight) / 2);
            $cropRemainder = (int)(($tempHeight - $newHeight) % 2);

            $this->crop($cropAmount + $cropRemainder, 0, 0, $cropAmount);
        }
    }
}
