<?php

declare(strict_types=1);

namespace Web200\ImageResize\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package   Web200\ImageResize\Provider
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Config
{
    /**
     * Constrain only
     *
     * @var string CONSTRAIN_ONLY
     */
    public const CONSTRAIN_ONLY = 'w200_image_resize/default/constrain_only';
    /**
     * Keep aspect ratio
     *
     * @var string KEEP_ASPECT_RATIO
     */
    public const KEEP_ASPECT_RATIO = 'w200_image_resize/default/keep_aspect_ratio';
    /**
     * Keep transparency
     *
     * @var string KEEP_TRANSPARENCY
     */
    public const KEEP_TRANSPARENCY = 'w200_image_resize/default/keep_transparency';
    /**
     * Keep frame
     *
     * @var string KEEP_FRAME
     */
    public const KEEP_FRAME = 'w200_image_resize/default/keep_frame';
    /**
     * Background color
     *
     * @var string BACKGROUND_COLOR
     */
    public const BACKGROUND_COLOR = 'w200_image_resize/default/background_color';
    /**
     * Quality
     *
     * @var string QUALITY
     */
    public const QUALITY = 'w200_image_resize/default/quality';
    /**
     * Webp enabled
     *
     * @var string WEBP_ENABLED
     */
    public const WEBP_ENABLED = 'w200_image_resize/webp/enabled';
    /**
     * Webp quality
     *
     * @var string WEBP_QUALITY
     */
    public const WEBP_QUALITY = 'w200_image_resize/webp/quality';
    /**
     * Webp converter
     *
     * @var string WEBP_CONVERTER
     */
    public const WEBP_CONVERTER = 'w200_image_resize/webp/converter';
    /**
     * Scope config interface
     *
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * Config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     *
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get default constrain only
     *
     * @param mixed $store
     *
     * @return bool
     */
    public function getDefaultConstrainOnly($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONSTRAIN_ONLY,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get default keep aspect ratio
     *
     * @param mixed $store
     *
     * @return bool
     */
    public function getDefaultKeepAspectRatio($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::KEEP_ASPECT_RATIO,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get default keep transparency
     *
     * @param mixed $store
     *
     * @return bool
     */
    public function getDefaultKeepTransparency($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::KEEP_TRANSPARENCY,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get default keep frame
     *
     * @param mixed $store
     *
     * @return bool
     */
    public function getDefaultKeepFrame($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::KEEP_FRAME,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get default background color
     *
     * @param mixed $store
     *
     * @return array|null
     */
    public function getBackgroundColor($store = null): ?array
    {
        /** @var string $colors */
        $colors = $this->scopeConfig->getValue(
            self::BACKGROUND_COLOR,
            ScopeInterface::SCOPE_STORES,
            $store
        );
        if (empty($colors)) {
            return null;
        }
        $colorsArray = explode(',', $colors);

        if (count($colorsArray) === 3) {
            return array_map('intval', $colorsArray);
        }

        return null;
    }

    /**
     * Get quality
     *
     * @param mixed $store
     *
     * @return int
     */
    public function getQuality($store = null): int
    {
        $quality = (int)$this->scopeConfig->getValue(
            self::QUALITY,
            ScopeInterface::SCOPE_STORES,
            $store
        );

        if ($quality < 70 || $quality > 100) {
            $quality = 70;
        }

        return $quality;
    }

    /**
     * Is webp enabled
     *
     * @param mixed $store
     *
     * @return bool
     */
    public function isWebpEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::WEBP_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get webp quality
     *
     * @param mixed $store
     *
     * @return int
     */
    public function getWebpQuality($store = null): int
    {
        $quality = (int)$this->scopeConfig->getValue(
            self::WEBP_QUALITY,
            ScopeInterface::SCOPE_STORES,
            $store
        );

        if ($quality < 70 || $quality > 100) {
            $quality = 70;
        }

        return $quality;
    }

    /**
     * Get webp converter
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getWebpConverter($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::WEBP_CONVERTER,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }
}
