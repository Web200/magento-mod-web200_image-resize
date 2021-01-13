<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Web200\ImageResize\Provider\Config;

/**
 * Class AuthorizeSvgUpload
 *
 * @package   Web200\ImageResize\Plugin
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class AuthorizeSvgUpload
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
     * @param NotProtectedExtension $subject
     * @param                       $result
     * @param Store|string|null     $store
     *
     * @return string[]
     */
    public function afterGetProtectedFileExtensions(
        NotProtectedExtension $subject,
        $result,
        $store = null
    ): array {

        if (!$this->config->isSvgEnabled()) {
            return $result;
        }
        unset($result['svg']);
        return $result;
    }
}
