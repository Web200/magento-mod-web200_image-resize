<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Store\Model\StoreManagerInterface;
use Web200\ImageResize\Provider\Config;
use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;

/**
 * Class Convertor
 *
 * @package   Web200\ImageResize\Model
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class WebpConvertor
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;
    /**
     * Write interface
     *
     * @var WriteInterface $mediaDirectoryRead
     */
    protected $mediaDirectoryRead;
    /**
     * Store manager interface
     *
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * WebpConvertor constructor.
     *
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     * @param Filesystem            $filesystem
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->mediaDirectoryRead  = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
    }

    /**
     * Convert
     *
     * @param string $image
     *
     * @return string
     */
    public function convert(string $image): string
    {
        try {
            /** @var string $webImage */
            $webImage = $this->getWebPImage($image);
            if (!$webImage) {
                return '';
            }

            if ($this->needsConversion($webImage)) {
                WebPConvert::convert($image, $webImage, $this->getOptions());
            }
            return $webImage;
        } catch (ConversionFailedException $exception) {
            return '';
        }
    }

    /**
     * Get webp image
     *
     * @param string $image
     *
     * @return string|null
     */
    public function getWebPImage(string $image): ?string
    {
        /** @var string[] $pathParts */
        $pathParts = pathinfo($image);
        if (preg_match('/^(jpg|jpeg|png)$/', $pathParts['extension'])) {
            return preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image);
        }

        return null;
    }

    /**
     * Needs conversion
     *
     * @param string $webImage
     *
     * @return bool
     * @throws NotFoundException
     */
    protected function needsConversion(string $webImage): bool
    {
        if (!file_exists($webImage)) {
            return true;
        }

        return false;
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'quality'     => 'auto',
            'max-quality' => $this->config->getWebpQuality(),
            'converters'  => [$this->config->getWebpConverter()],
        ];
    }

    /**
     * Is webp image exist
     *
     * @param string $webpImageUrl
     *
     * @return bool
     */
    public function isWebpImageExist(string $webpImageUrl): bool
    {
        /** @var string $mediaUrl */
        $mediaUrl  = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        /** @var string $mediaPath */
        $mediaPath = parse_url($mediaUrl, PHP_URL_PATH);
        /** @var string $imagePath */
        $imagePath = parse_url($webpImageUrl, PHP_URL_PATH);

        if (0 === strpos($imagePath, $mediaPath)) {
            /** @var string $webpImagePath */
            $webpImagePath = $this->mediaDirectoryRead->getAbsolutePath(substr_replace($imagePath, '', 0, strlen($mediaPath)));
            return file_exists($webpImagePath);
        }

        return false;
    }
}
