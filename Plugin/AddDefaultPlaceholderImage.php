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
     * @param Image      $subject
     * @param array|null $prefixes
     *
     * @return array
     */
    public function afterGetPrefixes(Image $subject, ?array $prefixes): array
    {
        $prefixes = $prefixes ?? [];

        foreach ($prefixes as $prefix) {
            if (isset($prefix['field']) && $prefix['field'] === 'default_') {
                return $prefixes;
            }
        }

        $prefixes[] = [
            'field' => 'default_',
            'label' => 'Non Product Image placeholder',
        ];

        return $prefixes;
    }
}
