<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

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
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Cache constructor.
     *
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Delete Image resizer cache dir
     */
    public function clearResizedImagesCache(): void
    {
        $this->mediaDirectory->delete(Resizer::IMAGE_RESIZE_CACHE_DIR);
    }
}
