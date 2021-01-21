<?php

namespace Web200\ImageResize\Plugin;

use Magento\Swatches\Helper\Media;
use Web200\ImageResize\Provider\Config;

class SvgSwatchMediaHelper
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * AuthorizeSvgUpload constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Generate swatch variations
     *
     * @param Media    $subject
     * @param callable $proceed
     * @param string   $imageUrl
     */
    public function aroundGenerateSwatchVariations(
        Media $subject,
        callable $proceed,
        $imageUrl
    ) {
        /** @var string[] $pathParts */
        $pathParts = pathinfo($imageUrl);
        if ($pathParts['extension'] === 'svg' && $this->config->isSvgEnabled()) {
            // Don't generate swatch variations
            return;
        }

        return $proceed($imageUrl);
    }
}
