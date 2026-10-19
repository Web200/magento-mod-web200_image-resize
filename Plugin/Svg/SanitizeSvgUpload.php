<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Closure;
use enshrined\svgSanitize\Sanitizer;
use Magento\Framework\File\Uploader;
use Web200\ImageResize\Provider\Config;

/**
 * Class SanitizeSvgUpload
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2026 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class SanitizeSvgUpload
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * SanitizeSvgUpload constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Around save
     *
     * @param Uploader $subject
     * @param callable $proceed
     * @param mixed    $destinationFolder
     * @param mixed    $newFileName
     *
     * @return bool|array
     */
    public function aroundSave(
        Uploader $subject,
        callable $proceed,
        $destinationFolder,
        $newFileName = null
    ) {
        if (strtolower((string)$subject->getFileExtension()) === 'svg' && $this->config->isSvgEnabled()) {
            $this->sanitizeTmpImages($subject);
        }

        return $proceed($destinationFolder, $newFileName);
    }

    /**
     * Sanitize tmp images
     *
     * @param Uploader $uploader
     *
     * @return void
     */
    protected function sanitizeTmpImages(Uploader $uploader): void
    {
        $tmpFile = $this->getTmpFile($uploader);
        if (isset($tmpFile['tmp_name']) && is_file($tmpFile['tmp_name'])) {
            $sanitizer = new Sanitizer();
            $dirtySVG = file_get_contents($tmpFile['tmp_name']);
            if ($dirtySVG !== false) {
                $cleanSVG = $sanitizer->sanitize($dirtySVG);
                if ($cleanSVG !== false && is_string($cleanSVG)) {
                    file_put_contents($tmpFile['tmp_name'], $cleanSVG);
                }
            }
        }
    }

    /**
     * Get tmp file
     *
     * @param Uploader $uploader
     *
     * @return null|array
     */
    protected function getTmpFile(Uploader $uploader): ?array
    {
        $closure = Closure::bind(function (Uploader $uploader) {
            return $uploader->_file;
        }, null, Uploader::class);

        return $closure($uploader);
    }

    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param Uploader $subject
     * @param mixed    $return
     * @param array    $validTypes
     *
     * @return bool
     */
    public function afterCheckMimeType(Uploader $subject, $return, $validTypes = [])
    {
        if (strtolower((string)$subject->getFileExtension()) === 'svg' && $this->config->isSvgEnabled()) {
            return true;
        }

        return (bool)$return;
    }

    /**
     * Check if specified extension is allowed
     *
     * @param Uploader $subject
     * @param mixed    $return
     * @param string   $extension
     *
     * @return bool
     */
    public function afterCheckAllowedExtension(Uploader $subject, $return, $extension)
    {
        if (strtolower((string)$subject->getFileExtension()) === 'svg' && $this->config->isSvgEnabled()) {
            return true;
        }

        return (bool)$return;
    }
}
