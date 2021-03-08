<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Web200\ImageResize\Provider\Config;

/**
 * Class DontResizeStorageImage
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class DontResizeStorageImage
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;
    /**
     * Directory
     *
     * @var Write $directory
     */
    protected $directory;
    /**
     * Cms wysiwyg images
     *
     * @var Images
     */
    protected $cmsWysiwygImages = null;

    /**
     * AuthorizeSvgUpload constructor.
     *
     * @param Config     $config
     * @param Filesystem $filesystem
     * @param Images     $cmsWysiwygImages
     *
     * @throws FileSystemException
     */
    public function __construct(
        Config $config,
        Filesystem $filesystem,
        Images $cmsWysiwygImages
    ) {
        $this->config           = $config;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
        $this->directory        = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Around resize file
     *
     * @param Storage  $subject
     * @param callable $proceed
     * @param string   $source
     * @param bool     $keepRatio
     *
     * @return bool
     */
    public function aroundResizeFile(
        Storage $subject,
        callable $proceed,
        $source,
        $keepRatio = true
    ) {
        if ((string)$source !== '') {
            $pathParts = pathinfo($source);
            if (isset($pathParts['extension'])) {
                if ($pathParts['extension'] === 'svg' && $this->config->isSvgEnabled()) {
                    return true;
                }
            }
        }

        return $proceed($source, $keepRatio);
    }

    /**
     * Authorize svg upload
     *
     * @param Storage $subject
     * @param         $result
     *
     * @return mixed
     */
    public function afterGetAllowedExtensions(Storage $subject, $result)
    {
        if (!$this->config->isSvgEnabled()) {
            return $result;
        }
        $result[] = 'svg';

        return $result;
    }

    /**
     * Authorize svg upload
     *
     * @param Storage $subject
     * @param         $result
     * @param string  $filePath
     * @param bool    $checkFile
     *
     * @return string
     * @throws ValidatorException
     */
    public function afterGetThumbnailUrl(Storage $subject, $result, $filePath, $checkFile = false)
    {
        $pathParts = pathinfo($filePath);
        if (isset($pathParts['extension'])) {
            if ($pathParts['extension'] === 'svg' && $this->config->isSvgEnabled()) {
                $thumbRelativePath = ltrim($this->directory->getRelativePath($filePath), '/\\');
                $baseUrl           = rtrim($this->cmsWysiwygImages->getBaseUrl(), '/');
                $randomIndex       = '?rand=' . time();

                return str_replace('\\', '/', $baseUrl . '/' . $thumbRelativePath) . $randomIndex;
            }
        }

        return $result;
    }
}
