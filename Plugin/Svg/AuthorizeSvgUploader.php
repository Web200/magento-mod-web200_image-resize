<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Backend\Block\Media\Uploader;

/**
 * Class AuthorizeSvgUploader
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class AuthorizeSvgUploader
{
    /**
     * Authorize svg uploader
     *
     * @param Uploader                              $subject
     * @param                                       $result
     *
     * @return string[]
     */
    public function afterGetConfig(Uploader $subject, $result)
    {
        /** @var string[] $filters */
        $filters = $result->getData('filters');
        if (is_array($filters) && isset($filters['images']['files'])) {
            $filters['images']['files'][] = '*.svg';
            $result->setData('filters', $filters);
        }

        return $result;
    }
}
