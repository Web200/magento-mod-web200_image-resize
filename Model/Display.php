<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Web200\ImageResize\Provider\Config;

/**
 * Class Display
 *
 * @package   Web200\ImageResize\Model
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Display
{
    /**
     * Placeholder factory
     *
     * @var PlaceholderFactory $placeholderFactory
     */
    protected $placeholderFactory;
    /**
     * Resize
     *
     * @var Resize $resize
     */
    protected $resize;
    /**
     * Filesystem
     *
     * @var Filesystem $filesystem
     */
    protected $filesystem;
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;
    /**
     * Webp convertor
     *
     * @var WebpConvertor $webpConvertor
     */
    protected $webpConvertor;

    /**
     * Display constructor.
     *
     * @param Resize             $resize
     * @param PlaceholderFactory $placeholderFactory
     * @param Filesystem         $filesystem
     * @param Config             $config
     * @param WebpConvertor      $webpConvertor
     */
    public function __construct(
        Resize $resize,
        PlaceholderFactory $placeholderFactory,
        Filesystem $filesystem,
        Config $config,
        WebpConvertor $webpConvertor
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->resize             = $resize;
        $this->filesystem         = $filesystem;
        $this->config             = $config;
        $this->webpConvertor      = $webpConvertor;
    }

    /**
     * Get media pPath
     *
     * @return string
     */
    public function getMediaPath(): string
    {
        return (string)$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Image
     *
     * ->getImage($imagePath, $width, $height, ['retina' => true, 'breakpoints' => ['1440' => ['100', '200'], '768' => ['100', '200']]])
     *
     * @param string $imagePath
     * @param int    $width
     * @param int    $height
     * @param array  $params
     *
     * @return string
     */
    public function getImage(string $imagePath, int $width, int $height, array $params = []): string
    {
        /** @var bool $retina */
        $resize = $params['resize'] ?? [];
        /** @var bool $retina */
        $retina = $params['retina'] ?? false;
        /** @var string $title */
        $title = $params['title'] ?? '';
        /** @var string $placeholderType */
        $placeholderType = $params['placeholder_type'] ?? 'default';
        /** @var string $alt */
        $alt = $params['alt'] ?? $title;
        /** @var string $class */
        $class = isset($params['class']) ? 'lazy ' . $params['class'] : 'lazy';
        /** @var string[] $breakpoints */
        $breakpoints = $params['breakpoints'] ?? [];

        /** @var bool $placeholder */
        $placeholder = (bool)($params['placeholder'] ?? true);
        /** @var string $placeholderImagePath */
        $placeholderImagePath = (string)$this->placeholderFactory->create(['type' => $placeholderType])->getPath();
        /** @var string $placeholderImageUrl */
        $placeholderImageUrl = $this->resize->resizeAndGetUrl($placeholderImagePath, $width, $height, $resize);

        /** @var string $mainImageUrl */
        $mainImageUrl = $this->resize->resizeAndGetUrl($imagePath, $width, $height, $resize);
        if ($mainImageUrl === '') {
            $imagePath = $placeholderImagePath;
            $mainImageUrl = $placeholderImageUrl;
        }

        if ($this->isSvgImage($imagePath)) {
            return '<picture><img alt="' . $alt . '" title="' . $title . '" class="' . $class . '" ' . ($placeholder ? '  src="' . $placeholderImageUrl . '"' : '') . '  data-src="' . $mainImageUrl . '"/></picture>';
        }

        /** @var string $html */
        $html = '<picture>';
        if (is_array($breakpoints) && !empty($breakpoints)) {
            /** @var int $bpWidth */
            /** @var int $bpHeight */
            foreach ($breakpoints as $breakpoint => [$bpWidth, $bpHeight]) {
                $html     .= '<source media="(min-width: ' . $breakpoint . 'px)" data-srcset="';
                $imageUrl = $this->resize->resizeAndGetUrl($imagePath, $bpWidth, $bpHeight, $resize);
                $imageUrl = $this->getWebpImage($imageUrl);
                $html     .= $imageUrl;
                if ($retina) {
                    $imageUrl = $this->resize->resizeAndGetUrl($imagePath, $bpWidth * 2, $bpHeight * 2, $resize);
                    $imageUrl = $this->getWebpImage($imageUrl);
                    $html     .= ' 1x, ' . $imageUrl . ' 2x';
                }
                $html .= '" />';
            }
        }

        /** @var string $mainSrcset */
        $mainSrcset = '';
        if ($retina) {
            $imageUrl   = $this->resize->resizeAndGetUrl($imagePath, $width * 2, $height * 2, $resize);
            $imageUrl   = $this->getWebpImage($imageUrl);
            $mainSrcset = $mainImageUrl . ' 1x, ' . $imageUrl . ' 2x';
        }

        $html .= '<img alt="' . $alt . '" title="' . $title . '" class="' . $class . '" ' . ($placeholder ? '  src="' . $placeholderImageUrl . '"' : '') . ' data-src="' . $mainImageUrl . '"  data-srcset="' . $mainSrcset . '"/>';
        $html .= '</picture>';

        return $html;
    }

    /**
     * Is svg image
     *
     * @param string $imagePath
     *
     * @return bool
     */
    protected function isSvgImage(string $imagePath): bool
    {
        /** @var string  $imageParts */
        $imageParts = pathinfo($imagePath);
        return isset($imageParts['extension']) && $imageParts['extension'] === 'svg';
    }

    /**
     * Get webp image if enabled
     *
     * @param $image
     *
     * @return string
     */
    protected function getWebpImage($image): string
    {
        if ($this->config->isWebpEnabled()) {
            $webPImage = $this->webpConvertor->getWebPImage($image);
            if ($webPImage !== '' && $this->webpConvertor->isWebpImageExist($webPImage)) {
                $image = $webPImage;
            }
        }

        return $image;
    }
}
