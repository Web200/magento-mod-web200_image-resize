<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Framework\File\Mime;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Web200\ImageResize\Provider\Config;

/**
 * Class ValidateSvgFile
 *
 * @package Web200\ImageResize\Plugin\Svg
 */
class ValidateSvgFile
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;
    /**
     * File mime
     *
     * @var Mime $fileMime
     */
    protected $fileMime;

    /**
     * SanitizeSvgUpload constructor.
     *
     * @param Config $config
     * @param Mime   $fileMime
     */
    public function __construct(
        Config $config,
        Mime $fileMime
    ) {
        $this->config = $config;
        $this->fileMime = $fileMime;
    }

    /**
     * Validate svg file
     *
     * @param AbstractAdapter $subject
     * @param callable        $proceed
     * @param string          $filePath
     *
     * @return mixed
     */
    public function aroundValidateUploadFile(
        AbstractAdapter $subject,
        callable $proceed,
        $filePath
    ) {
        $mimeType = $this->fileMime->getMimeType($filePath);
        if ($mimeType === 'image/svg+xml' && $this->config->isSvgEnabled()) {
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException('Upload file does not exist.');
            }

            if (filesize($filePath) === 0) {
                throw new \InvalidArgumentException('Wrong file size.');
            }
        } else {
            return $proceed($filePath);
        }
    }
}
