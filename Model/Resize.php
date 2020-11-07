<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory as imageAdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Web200\ImageResize\Provider\Config;

/**
 * Class Resize
 *
 * @package   Web200\ImageResize\Model
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Resize
{
    /**
     * constant IMAGE_RESIZE_DIR
     */
    public const IMAGE_RESIZE_DIR = 'web200_imageresize';
    /**
     * constant IMAGE_RESIZE_CACHE_DIR
     */
    public const IMAGE_RESIZE_CACHE_DIR = self::IMAGE_RESIZE_DIR . '/' . DirectoryList::CACHE;
    /**
     * @var imageAdapterFactory
     */
    protected $imageAdapterFactory;
    /**
     * @var array
     */
    protected $resizeSettings = [];
    /**
     * @var string
     */
    protected $relativeFilename;
    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectoryRead;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var array
     *
     * - constrainOnly[true]: Guarantee, that image picture will not be bigger, than it was. It is false by default.
     * - keepAspectRatio[true]: Guarantee, that image picture width/height will not be distorted. It is true by default.
     * - keepTransparency[true]: Guarantee, that image will not lose transparency if any. It is true by default.
     * - keepFrame[false]: Guarantee, that image will have dimensions, set in $width/$height. Not applicable,
     * if keepAspectRatio(false).
     * - backgroundColor[null]: Default white
     */
    protected $defaultSettings = [
        'constrainOnly'    => true,
        'keepAspectRatio'  => true,
        'keepTransparency' => true,
        'keepFrame'        => false,
        'backgroundColor'  => null,
        'quality'          => 85
    ];
    /**
     * @var array
     */
    protected $subPathSettingsMapping = [
        'constrainOnly'    => 'co',
        'keepAspectRatio'  => 'ar',
        'keepTransparency' => 'tr',
        'keepFrame'        => 'fr',
        'backgroundColor'  => 'bc',
    ];
    /**
     * @var File
     */
    protected $fileIo;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * Resizer constructor.
     *
     * @param Filesystem            $filesystem
     * @param ImageAdapterFactory   $imageAdapterFactory
     * @param StoreManagerInterface $storeManager
     * @param File                  $fileIo
     * @param LoggerInterface       $logger
     * @param Config                $config
     */
    public function __construct(
        Filesystem $filesystem,
        imageAdapterFactory $imageAdapterFactory,
        StoreManagerInterface $storeManager,
        File $fileIo,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->mediaDirectoryRead  = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->storeManager        = $storeManager;
        $this->fileIo              = $fileIo;
        $this->logger              = $logger;
        $this->config              = $config;
    }

    /**
     * Resized image and return url
     * - Return original image url if no success
     *
     * @param string   $imagePath
     * @param null|int $width
     * @param null|int $height
     * @param array    $resizeSettings
     *
     * @return string
     */
    public function resizeAndGetUrl(string $imagePath, $width, $height, array $resizeSettings = []): string
    {
        /** @var string $resultUrl */
        $resultUrl = '';
        try {
            if (strpos($imagePath, 'http') !== 0) {
                $this->relativeFilename = str_replace($this->mediaDirectoryRead->getAbsolutePath(), '', $imagePath);
            } else {
                // Set $resultUrl with $fileUrl to return this one in case the resize fails.
                $resultUrl = $imagePath;
                $this->initRelativeFilenameFromUrl($imagePath);
                if (!$this->relativeFilename) {
                    return $resultUrl;
                }

                // Check if image is an animated gif return original gif instead of resized still.
                if ($this->isAnimatedGif($imagePath)) {
                    return $resultUrl;
                }
            }
            $this->initSize($width, $height);
            $this->initResizeSettings($resizeSettings);
        } catch (\Exception $e) {
            $this->logger->addError("Web200_ImageResize: could not find image: \n" . $e->getMessage());
        }
        try {
            // Check if resized image already exists in cache
            $resizedUrl = $this->getResizedImageUrl();
            if (!$resizedUrl && $this->resizeAndSaveImage()) {
                $resizedUrl = $this->getResizedImageUrl();
            }
            if ($resizedUrl) {
                $resultUrl = $resizedUrl;
            }
        } catch (\Exception $e) {
            $this->logger->addError("Web200_ImageResize: could not resize image: \n" . $e->getMessage());
        }

        return $resultUrl;
    }

    /**
     * Prepare and set resize settings for image
     *
     * @param array $resizeSettings
     */
    protected function initResizeSettings(array $resizeSettings): void
    {
        // Init resize settings with default
        $this->resizeSettings = [
            'constrainOnly'    => $this->config->getDefaultConstrainOnly(),
            'keepAspectRatio'  => $this->config->getDefaultKeepAspectRatio(),
            'keepTransparency' => $this->config->getDefaultKeepTransparency(),
            'keepFrame'        => $this->config->getDefaultKeepFrame(),
            'backgroundColor'  => $this->config->getBackgroundColor(),
            'quality'          => $this->config->getQuality()
        ];
        // Override resizeSettings only if key matches with existing settings
        foreach ($resizeSettings as $key => $value) {
            if (array_key_exists($key, $this->resizeSettings)) {
                $this->resizeSettings[$key] = $value;
            }
        }
    }

    /**
     * Init relative filename from original image url to resize
     *
     * @param string $imageUrl
     *
     * @return bool|mixed|string
     * @throws NoSuchEntityException
     */
    protected function initRelativeFilenameFromUrl(string $imageUrl): void
    {
        $this->relativeFilename = false; // reset filename in case there was another value defined
        $mediaUrl               = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaPath              = parse_url($mediaUrl, PHP_URL_PATH);
        $imagePath              = parse_url($imageUrl, PHP_URL_PATH);

        if (0 === strpos($imagePath, $mediaPath)) {
            $this->relativeFilename = substr_replace($imagePath, '', 0, strlen($mediaPath));
        }
    }

    /**
     * Init resize dimensions
     *
     * @param null|int $width
     * @param null|int $height
     */
    protected function initSize($width, $height): void
    {
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Get sub folder name where the resized image will be saved
     *
     * In order to have unique folders depending on setting, we use the following logic:
     *      - <width>x<height>_[co]_[ar]_[tr]_[fr]_[quality]
     *
     * @return string
     */
    protected function getResizeSubFolderName(): string
    {
        $subPath = $this->width . "x" . $this->height;
        foreach ($this->resizeSettings as $key => $value) {
            if ($value && isset($this->subPathSettingsMapping[$key])) {
                $subPath .= "_" . $this->subPathSettingsMapping[$key];
            }
        }

        return sprintf('%s_%s', $subPath, $this->resizeSettings['quality']);
    }

    /**
     * Get relative path where the resized image is saved
     *
     * In order to have unique paths, we use the original image path plus the ResizeSubFolderName.
     *
     * @return string
     */
    protected function getRelativePathResizedImage(): string
    {
        $pathInfo          = $this->fileIo->getPathInfo($this->relativeFilename);
        $relativePathParts = [
            self::IMAGE_RESIZE_CACHE_DIR,
            $pathInfo['dirname'],
            $this->getResizeSubFolderName(),
            $pathInfo['basename']
        ];

        return implode('/', $relativePathParts);
    }

    /**
     * Get absolute path from original image
     *
     * @return string
     */
    protected function getAbsolutePathOriginal(): string
    {
        return $this->mediaDirectoryRead->getAbsolutePath($this->relativeFilename);
    }

    /**
     * Get absolute path from resized image
     *
     * @return string
     */
    protected function getAbsolutePathResized(): string
    {
        return $this->mediaDirectoryRead->getAbsolutePath($this->getRelativePathResizedImage());
    }

    /**
     * Get url of resized image
     *
     * @return bool|string
     * @throws NoSuchEntityException
     */
    protected function getResizedImageUrl(): string
    {
        $relativePath = $this->getRelativePathResizedImage();
        if ($this->mediaDirectoryRead->isFile($relativePath)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativePath;
        }

        return '';
    }

    /**
     * Resize and save new generated image
     *
     * @return bool
     * @throws \Exception
     */
    protected function resizeAndSaveImage(): bool
    {
        if (!$this->mediaDirectoryRead->isFile($this->relativeFilename)) {
            return false;
        }

        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($this->getAbsolutePathOriginal());
        $imageAdapter->constrainOnly($this->resizeSettings['constrainOnly']);
        $imageAdapter->keepAspectRatio($this->resizeSettings['keepAspectRatio']);
        $imageAdapter->keepTransparency($this->resizeSettings['keepTransparency']);
        $imageAdapter->keepFrame($this->resizeSettings['keepFrame']);
        $imageAdapter->backgroundColor($this->resizeSettings['backgroundColor']);
        $imageAdapter->quality($this->resizeSettings['quality']);
        $imageAdapter->resize($this->width, $this->height);
        $imageAdapter->save($this->getAbsolutePathResized());

        return true;
    }

    /**
     * Detects animated GIF from given file pointer resource or filename.
     *
     * @param resource|string $file File pointer resource or filename
     *
     * @return bool
     */
    protected function isAnimatedGif($file): bool
    {
        $filepointer = null;

        if (is_string($file)) {
            if (strpos(strtolower($file), '.gif') === false) {
                return false;
            }
            $filepointer = fopen($file, "rb");
        } else {
            $filepointer = $file;
            /* Make sure that we are at the beginning of the file */
            fseek($filepointer, 0);
        }

        if (fread($filepointer, 3) !== "GIF") {
            fclose($filepointer);

            return false;
        }

        $frames = 0;

        while (!feof($filepointer) && $frames < 2) {
            if (fread($filepointer, 1) === "\x00") {
                /* Some of the animated GIFs do not contain graphic control extension (starts with 21 f9) */
                if (fread($filepointer, 1) === "\x21" || fread($filepointer, 2) === "\x21\xf9") {
                    $frames++;
                }
            }
        }

        fclose($filepointer);

        return $frames > 1;
    }
}
