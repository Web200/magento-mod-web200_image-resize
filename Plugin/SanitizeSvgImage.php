<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin;

use enshrined\svgSanitize\Sanitizer;
use Magento\Framework\File\Uploader;

/**
 * Class SanitizeSvgImage
 *
 * @package   Web200\ImageResize\Plugin
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class SanitizeSvgImage
{
    /**
     * Sanitize Svg Image
     *
     * @param Uploader    $subject
     * @param string      $destinationFolder
     * @param string|null $newFileName
     *
     * @return array
     */
    public function beforeSave(Uploader $subject, $destinationFolder, $newFileName = null)
    {
        if ($subject->getFileExtension() === 'svg') {
            $this->sanitizeTmpImages();
        }

        return [$destinationFolder, $newFileName];
    }

    /**
     * Sanitize tmp images
     *
     * @return void
     */
    protected function sanitizeTmpImages(): void
    {
        /** @var Sanitizer $sanitizer */
        $sanitizer = new Sanitizer();
        foreach ($_FILES as $file) {
            if (isset($file['tmp_name'])) {
                /** @var string $dirtySVG */
                $dirtySVG = file_get_contents($file['tmp_name']);
                /** @var string $cleanSVG */
                $cleanSVG = $sanitizer->sanitize($dirtySVG);
                file_put_contents($file['tmp_name'], $cleanSVG);
            }
        }
    }
}
