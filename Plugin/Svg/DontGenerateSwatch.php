<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Swatches\Helper\Media;
use Web200\ImageResize\Provider\Config;

/**
 * Class DontGenerateSwatch
 *
 * @package   Web200\ImageResize\Plugin\Svg
 */
class DontGenerateSwatch
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected Config $config
    ) {
    }

    /**
     * Generate swatch variations
     *
     * @param Media    $subject
     * @param callable $proceed
     * @param string   $imageUrl
     *
     * @return void
     */
    public function aroundGenerateSwatchVariations(
        Media $subject,
        callable $proceed,
        $imageUrl
    ) {
        if ($imageUrl) {
            $pathParts = pathinfo($imageUrl);
            if (isset($pathParts['extension']) &&
                strtolower($pathParts['extension']) === 'svg' &&
                $this->config->isSvgEnabled()
            ) {
                return;
            }
        }

        return $proceed($imageUrl);
    }
}
