<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin\Svg;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Web200\ImageResize\Provider\Config;

/**
 * Class AuthorizeSvgUploadValidator
 *
 * @package   Web200\ImageResize\Plugin\Svg
 * @author    Web200 <contact@web200.fr>
 * @copyright 2021 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class AuthorizeSvgUploadValidator
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * AuthorizeSvgUploadValidator constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Authorize svg upload by removing svg from protected extensions
     *
     * @param NotProtectedExtension $subject
     * @param string|array|null     $result
     * @param mixed                 $store
     *
     * @return string|array|null
     */
    public function afterGetProtectedFileExtensions(
        NotProtectedExtension $subject,
        $result,
        $store = null
    ) {
        if (!$this->config->isSvgEnabled()) {
            return $result;
        }

        if (is_string($result)) {
            $extensions = array_map('trim', explode(',', $result));
            $extensions = array_filter($extensions, static fn($ext) => strtolower($ext) !== 'svg');
            return implode(',', $extensions);
        }

        if (is_array($result)) {
            if (isset($result['svg'])) {
                unset($result['svg']);
            }
            return array_filter($result, static fn($ext) => strtolower(trim((string)$ext)) !== 'svg');
        }

        return $result;
    }
}
