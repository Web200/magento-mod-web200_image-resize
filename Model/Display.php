<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

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
     * resize
     *
     * @var Resize $resize
     */
    protected $resize;
    /**
     * filesystem
     *
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * Display constructor.
     *
     * @param Resize             $resize
     * @param PlaceholderFactory $placeholderFactory
     * @param Filesystem         $filesystem
     */
    public function __construct(
        Resize $resize,
        PlaceholderFactory $placeholderFactory,
        Filesystem $filesystem
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->resize             = $resize;
        $this->filesystem         = $filesystem;
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
        /** @var string $alt */
        $alt = $params['alt'] ?? $title;
        /** @var string $class */
        $class = isset($params['class']) ? 'lazy ' . $params['class'] : 'lazy';
        /** @var string[] $breakpoints */
        $breakpoints = $params['breakpoints'] ?? [];
        /** @var string $imagePath */
        /** @var string $html */
        $html = '<picture>';
        /** @var string $placeholderImagePath */
        $placeholderImagePath = $this->placeholderFactory->create(['type' => 'image'])->getPath();
        /** @var string $placeholderImageUrl */
        $placeholderImageUrl = $this->resize->resizeAndGetUrl($placeholderImagePath, $width, $height, $resize);

        /** @var string $mainImageUrl */
        $mainImageUrl = $this->resize->resizeAndGetUrl($imagePath, $width, $height, $resize);
        if ($mainImageUrl === '') {
            $imagePath = $placeholderImagePath;
        }

        if (is_array($breakpoints) && !empty($breakpoints)) {
            /** @var int $bpWidth */
            /** @var int $bpHeight */
            foreach ($breakpoints as $breakpoint => [$bpWidth, $bpHeight]) {
                $html     .= '<source media="(min-width: ' . $breakpoint . 'px)" data-srcset="';
                $imageUrl = $this->resize->resizeAndGetUrl($imagePath, $bpWidth, $bpHeight, $resize);
                $html     .= $imageUrl;
                if ($retina) {
                    $imageUrl = $this->resize->resizeAndGetUrl($imagePath, $bpWidth * 2, $bpHeight * 2, $resize);
                    $html     .= ' 1x, ' . $imageUrl . ' 2x';
                }
                $html .= '" />';
            }
        }

        /** @var string $mainSrcset */
        $mainSrcset = '';
        if ($retina) {
            $imageUrl   = $this->resize->resizeAndGetUrl($imagePath, $width * 2, $height * 2, $resize);
            $mainSrcset = $mainImageUrl . ' 1x, ' . $imageUrl . ' 2x';
        }

        $html .= '<img alt="' . $alt . '" title="' . $title . '" class="' . $class . '" src="' . $placeholderImageUrl . '" data-src="' . $mainImageUrl . '"  data-srcset="' . $mainSrcset . '"/>';
        $html .= '</picture>';

        return $html;
    }
}
