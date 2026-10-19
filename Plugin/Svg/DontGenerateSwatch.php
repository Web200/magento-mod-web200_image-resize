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
 * @copyright 2026 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
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
     * @return Media
     */
    public function aroundGenerateSwatchVariations(
        Media $subject,
        callable $proceed,
        $imageUrl
    ) {
        if ($imageUrl) {
            $pathParts = pathinfo((string)$imageUrl);
            if (isset($pathParts['extension']) &&
                strtolower($pathParts['extension']) === 'svg' &&
                $this->config->isSvgEnabled()
            ) {
                return $subject;
            }
        }

        return $proceed($imageUrl);
    }
}
