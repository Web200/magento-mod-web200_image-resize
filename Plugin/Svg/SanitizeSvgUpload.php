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
 * @package   Web200\ImageResize\Plugin
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
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
     * @param          $destinationFolder
     * @param null     $newFileName
     *
     * @return bool|string[]
     */
    public function aroundSave(
        Uploader $subject,
        callable $proceed,
        $destinationFolder,
        $newFileName = null
    ) {
        if ($subject->getFileExtension() === 'svg' && $this->config->isSvgEnabled()) {
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
        /** @var Sanitizer $sanitizer */
        $sanitizer = new Sanitizer();

        /** @var bool $tmpFile */
        $tmpFile = $this->getTmpFile($uploader);
        if (isset($tmpFile['tmp_name'])) {
            /** @var string $dirtySVG */
            $dirtySVG = file_get_contents($tmpFile['tmp_name']);
            /** @var string $cleanSVG */
            $cleanSVG = $sanitizer->sanitize($dirtySVG);
            file_put_contents($tmpFile['tmp_name'], $cleanSVG);
        }
    }

    /**
     * Get tmp file
     *
     * @param Uploader $uploader
     *
     * @return null|string[]
     */
    protected function getTmpFile(Uploader $uploader): ?array
    {
        /** @var bool $closure */
        $closure = Closure::bind(function (Uploader $uploader) {
            return $uploader->_file;
        }, null, Uploader::class);

        return $closure($uploader);
    }

    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param Uploader $subject
     * @param          $return
     * @param string[] $validTypes
     */
    public function afterCheckMimeType(Uploader $subject, $return, $validTypes = [])
    {
        if ($subject->getFileExtension() === 'svg' && $this->config->isSvgEnabled()) {
            return true;
        }

        return $return;
    }

    /**
     * Check if specified extension is allowed
     *
     * @param Uploader $subject
     * @param          $return
     * @param string   $extension
     *
     * @return boolean
     */
    public function afterCheckAllowedExtension(Uploader $subject, $return, $extension)
    {
        if ($subject->getFileExtension() === 'svg' && $this->config->isSvgEnabled()) {
            return true;
        }

        return $return;
    }
}
