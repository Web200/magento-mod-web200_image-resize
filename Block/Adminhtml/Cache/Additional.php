<?php

declare(strict_types=1);

namespace Web200\ImageResize\Block\Adminhtml\Cache;

use Magento\Backend\Block\Cache\Additional as MagentoCacheAdditional;

/**
 * Class Additional
 *
 * @package   Web200\ImageResize\Block\Cache
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Additional extends MagentoCacheAdditional
{
    /**
     * Clean resized images url
     *
     * @return string
     */
    public function getCleanResizedImagesUrl(): string
    {
        return $this->getUrl('web200_imageresize/cache/cleanResizedImages');
    }
}
