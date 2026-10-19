<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Backend\Block\Media\Uploader;
use Web200\ImageResize\Provider\Config;

/**
 * Class AuthorizeSvgUploader
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2026 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class AuthorizeSvgUploader
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected Config $config
    ) {
    }

    /**
     * Authorize svg uploader
     *
     * @param Uploader $subject
     * @param mixed    $result
     *
     * @return mixed
     */
    public function afterGetConfig(Uploader $subject, $result)
    {
        if (!$this->config->isSvgEnabled()) {
            return $result;
        }

        /** @var array $filters */
        $filters = $result->getData('filters');
        if (is_array($filters) && isset($filters['images']['files']) && is_array($filters['images']['files'])) {
            if (!in_array('*.svg', $filters['images']['files'], true)) {
                $filters['images']['files'][] = '*.svg';
                $result->setData('filters', $filters);
            }
        }

        return $result;
    }
}
