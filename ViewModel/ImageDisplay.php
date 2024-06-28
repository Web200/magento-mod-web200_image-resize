<?php

declare(strict_types=1);

namespace Web200\ImageResize\ViewModel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Web200\ImageResize\Model\Display;

/**
 * Class ImageDisplay
 *
 * @package   Web200\ImageResize\ViewModel
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class ImageDisplay implements ArgumentInterface
{
    /**
     * ImageDisplay constructor.
     *
     * @param Display    $display
     * @param Repository $assetRepository
     * @param Filesystem $filesystem
     * @param State      $state
     */
    public function __construct(
        protected Display $display,
        protected Repository $assetRepository,
        protected Filesystem $filesystem,
        protected State $state
    ) {
    }

    /**
     * Get display
     *
     * @return Display
     */
    public function getDisplay(): Display
    {
        return $this->display;
    }

    /**
     * @param $imagePath
     *
     * @return string
     * @throws LocalizedException
     */
    public function getViewFilePath($imagePath): string
    {
        if ($this->isDeveloperMode()) {
            return $this->getViewFileUrl($imagePath);
        }
        $asset = $this->assetRepository->createAsset($imagePath);
        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        return $directoryRead->getAbsolutePath($asset->getPath());
    }

    /**
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        return $this->state->getMode() == State::MODE_DEVELOPER;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     *
     * @return string
     */
    public function getViewFileUrl(string $fileId)
    {
        try {
            return $this->assetRepository->getUrl($fileId);
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
