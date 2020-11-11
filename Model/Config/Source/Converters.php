<?php

declare(strict_types=1);

namespace Web200\ImageResize\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Converters
 *
 * @package   Web200\ImageResize\Model\Config\Source
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class Converters implements OptionSourceInterface
{
    /**
     * Options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'cwebp', 'label' => 'CWebP'],
            ['value' => 'gd', 'label' => 'GD php extension'],
            ['value' => 'imagick', 'label' => 'Image Magick'],
            ['value' => 'wpc', 'label' => 'WebPConvert Cloud Service'],
            ['value' => 'ewww', 'label' => 'ewww cloud converter']
        ];
    }
}
