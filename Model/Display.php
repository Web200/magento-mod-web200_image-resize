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
 * @copyright 2026 Web200
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
     * @var bool $retina
     */
    protected bool $retina = false;

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
     * Get media path
     *
     * @return string
     */
    public function getMediaPath(): string
    {
        return (string)$this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Image HTML
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
        $resize = $params['resize'] ?? [];
        $this->setRetina((bool)($params['retina'] ?? false));
        $title = (string)($params['title'] ?? '');
        $placeholderType = (string)($params['placeholder_type'] ?? 'default');
        $alt = (string)($params['alt'] ?? $title);
        $class = isset($params['class']) ? 'lazy ' . $params['class'] : 'lazy';
        $breakpoints = $params['breakpoints'] ?? [];

        $placeholder = (bool)($params['placeholder'] ?? true);
        $placeholderImagePath = (string)$this->placeholderFactory->create(['type' => $placeholderType])->getPath();
        $placeholderImageUrl = $this->resize->resizeAndGetUrl($placeholderImagePath, $width, $height, $resize);

        $mainImageUrl = $this->resize->resizeAndGetUrl($imagePath, $width, $height, $resize);
        if ($mainImageUrl === '') {
            $imagePath    = $placeholderImagePath;
            $mainImageUrl = $placeholderImageUrl;
        }

        if ($this->isSvgImage($imagePath)) {
            return '<picture><img loading="lazy" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '" title="' . htmlspecialchars($title, ENT_QUOTES) . '" class="' . htmlspecialchars($class, ENT_QUOTES) . '" ' . ($placeholder ? ' src="' . $placeholderImageUrl . '"' : '') . ' width="' . $width . '" height="' . $height . '" data-src="' . $mainImageUrl . '"/></picture>';
        }

        $html = '<picture>';
        if (is_array($breakpoints) && !empty($breakpoints)) {
            foreach ($breakpoints as $breakpoint => $dimensions) {
                if (!is_array($dimensions)) {
                    continue;
                }
                $bpWidth = isset($dimensions[0]) ? (int)$dimensions[0] : null;
                $bpHeight = isset($dimensions[1]) ? (int)$dimensions[1] : null;
                if (!$bpWidth || !$bpHeight) {
                    continue;
                }

                if ($this->config->isWebpEnabled()) {
                    $webpSrcset = $this->getBreakPointImages($imagePath, $bpWidth, $bpHeight, $resize, 'webp');
                    if ($webpSrcset !== '') {
                        $html .= '<source type="image/webp" media="(min-width: ' . (int)$breakpoint . 'px)" data-srcset="' . $webpSrcset . '" />';
                    }
                }
                $defaultSrcset = $this->getBreakPointImages($imagePath, $bpWidth, $bpHeight, $resize);
                if ($defaultSrcset !== '') {
                    $html .= '<source media="(min-width: ' . (int)$breakpoint . 'px)" data-srcset="' . $defaultSrcset . '" />';
                }
            }
        }

        if ($this->config->isWebpEnabled()) {
            $mainWebpSrcset = $this->getBreakPointImages($imagePath, $width, $height, $resize, 'webp');
            if ($mainWebpSrcset !== '') {
                $html .= '<source type="image/webp" data-srcset="' . $mainWebpSrcset . '" />';
            }
        }

        $mainSrcset = '';
        if ($this->isRetina()) {
            $mainSrcset = $this->getBreakPointImages($imagePath, $width, $height, $resize);
        }

        $html .= '<img loading="lazy" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '" title="' . htmlspecialchars($title, ENT_QUOTES) . '" class="' . htmlspecialchars($class, ENT_QUOTES) . '" ' . ($placeholder ? ' src="' . $placeholderImageUrl . '"' : '') . ' width="' . $width . '" height="' . $height . '" data-src="' . $mainImageUrl . '"' . ($mainSrcset !== '' ? ' data-srcset="' . $mainSrcset . '"' : '') . '/>';
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
        /** @var string[] $imageParts */
        $imageParts = pathinfo($imagePath);

        return isset($imageParts['extension']) && strtolower($imageParts['extension']) === 'svg';
    }

    /**
     * Get breakpoint images srcset string
     *
     * @param string      $imagePath
     * @param int         $bpWidth
     * @param int         $bpHeight
     * @param array       $resize
     * @param string|null $format
     *
     * @return string
     */
    protected function getBreakPointImages(string $imagePath, int $bpWidth, int $bpHeight, array $resize, ?string $format = null): string
    {
        $imageUrl = $this->resize->resizeAndGetUrl($imagePath, $bpWidth, $bpHeight, $resize, $format);
        if ($imageUrl === '') {
            return '';
        }

        if ($this->isRetina()) {
            $imageUrl2x = $this->resize->resizeAndGetUrl($imagePath, $bpWidth * 2, $bpHeight * 2, $resize, $format);
            if ($imageUrl2x !== '') {
                return $imageUrl . ' 1x, ' . $imageUrl2x . ' 2x';
            }
        }

        return $imageUrl;
    }

    /**
     * Get webp image if enabled
     *
     * @param string $image
     *
     * @return string
     */
    protected function getWebpImage(string $image): string
    {
        if ($this->config->isWebpEnabled()) {
            $webPImage = $this->webpConvertor->getWebPImage($image);
            if ($webPImage !== null && $this->webpConvertor->isWebpImageExist($webPImage)) {
                return $webPImage;
            }
        }

        return $image;
    }

    /**
     * @return bool
     */
    protected function isRetina(): bool
    {
        return $this->retina;
    }

    /**
     * @param bool $retina
     *
     * @return void
     */
    protected function setRetina(bool $retina): void
    {
        $this->retina = $retina;
    }
}
