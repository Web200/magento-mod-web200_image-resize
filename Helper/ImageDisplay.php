<?php

declare(strict_types=1);

namespace Web200\ImageResize\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Web200\ImageResize\Model\Display;

/**
 * Class ImageDisplay
 *
 * @package   Web200\ImageResize\Helper
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class ImageDisplay extends AbstractHelper
{
    /**
     * Display
     *
     * @var Display $display
     */
    protected $display;

    /**
     * ImageDisplay constructor.
     *
     * @param Display $display
     * @param Context $context
     */
    public function __construct(
        Display $display,
        Context $context
    ) {
        parent::__construct($context);

        $this->display = $display;
    }

    /**
     * Get display
     *
     * @return Display
     */
    public function getDisplay(): Display
    {
        return $this->display;
    }
}
