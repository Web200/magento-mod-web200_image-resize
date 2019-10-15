

# Magento 2 Image Resize

Magento 2 Module to add simple image resizing capabilities in all blocks and .phtml templates

## Installation

```
$ composer require "web200/magento-mod-web200_image-resize":"*"
```

## Usage

Layout
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="header.container">
            <block name="authlinks" template="Magento_Theme::authlinks.phtml">
                <arguments>
                    <argument name="image_resize" xsi:type="object">Web200\ImageResize\ViewModel\ImageResize</argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

phtml
```php
<?php /** @var \Web200\ImageResize\ViewModel\ImageResize $imageResize */ ?>
<?php $imageResize = $block->getImageResize() ?>
<?php 
/**
* $originalImage can be a full url image : https://mywebsite.com/pub/media/catalog/product/a/b/001.jpg
* or relative media path : catalog/product/a/b/001.jpg
*/
?>
<?php $imageResize->getResize()->resizeAndGetUrl($originalImage, $width, $height, [$resizeSettings])
```

## Cache

Resized images are saved in cache to improve performance. That way, if an image was already resized, we just use the one in cache.

If you need to, you can clear the resized images cache on the Admin Cache Management

![Admin Clear Resized Images Cache](docs/img/admin-clear-cache.png "Clear Resized Images Cache")

## Prerequisites

- PHP >= 7.1.*
- Magento >= 2.3.*

## Forked from 
https://github.com/staempfli/magento2-module-image-resizer