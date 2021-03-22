<?php

declare(strict_types=1);

namespace Web200\ImageResize\Plugin;

use Magento\Catalog\Model\Config\CatalogClone\Media\Image;

/**
 * Class AddDefaultPlaceholderImage
 *
 * @package Web200\ImageResize\Plugin
 */
class AddDefaultPlaceholderImage
{
    /**
     * Add default placeholder image
     *
     * @param Image    $subject
     * @param string[] $prefixes
     *
     * @return string[]
     */
    public function afterGetPrefixes(Image $subject, $prefixes)
    {
        if (is_array($prefixes)) {
            $prefixes[] = [
                'field' => 'default_',
                'label' => 'Non Product Image placeholder',
            ];
        }

        return $prefixes;
    }
}
