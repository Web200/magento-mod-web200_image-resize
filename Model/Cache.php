<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class Cache
 *
 * @package   Web200\ImageResize\Model
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Cache
{
    /**
     * @var WriteInterface $mediaDirectory
     */
    protected $mediaDirectory;
    /**
     * @var CacheInterface $cache
     */
    protected $cache;

    /**
     * Cache constructor.
     *
     * @param Filesystem     $filesystem
     * @param CacheInterface $cache
     *
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        CacheInterface $cache
    ) {
        $this->cache          = $cache;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Delete Image resizer cache dir
     */
    public function clearResizedImagesCache(): void
    {
        $this->cache->clean([Resize::CACHE_TAG_IDENTIFIER]);
        $this->mediaDirectory->delete(Resize::IMAGE_RESIZE_CACHE_DIR);
    }
}
