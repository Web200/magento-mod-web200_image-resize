<?php

declare(strict_types=1);

namespace Web200\ImageResize\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Web200\ImageResize\Model\Display;

/**
 * Class ImageDisplay
 *
 * @package   Web200\ImageResize\ViewModel
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class ImageDisplay implements ArgumentInterface
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
     */
    public function __construct(
        Display $display
    ) {
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
