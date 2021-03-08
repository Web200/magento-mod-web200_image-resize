<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\Theme\Model\Design\Backend\File;
use Web200\ImageResize\Provider\Config;

/**
 * Class AuthorizeSvgUploadCms
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class AuthorizeSvgUploadCms
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * AuthorizeSvgUpload constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Authorize svg upload
     *
     * @param File                                     $subject
     * @param                                          $result
     *
     * @return string[]
     */
    public function afterGetAllowedExtensions(File $subject, $result)
    {
        if (!$this->config->isSvgEnabled()) {
            return $result;
        }
        $result[] = 'svg';

        return $result;
    }
}
