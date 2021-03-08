<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Swatches\Helper\Media;
use Web200\ImageResize\Provider\Config;

/**
 * Class DontGenerateSwatch
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class DontGenerateSwatch
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * DontGenerateSwatch constructor.
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
