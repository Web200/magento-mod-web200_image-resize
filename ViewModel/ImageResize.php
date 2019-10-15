<?php

declare(strict_types=1);

namespace Web200\ImageResize\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Web200\ImageResize\Model\Resize;

/**
 * Class ImageResize
 *
 * @package   Web200\ImageResize\ViewModel
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class ImageResize implements ArgumentInterface
{
    /**
     * Description $resize field
     *
     * @var Resizer $resize
     */
    protected $resize;

    /**
     * Session constructor.
     *
     * @param Resize $resize
     */
    public function __construct(
        Resize $resize
    ) {
        $this->resize = $resize;
    }

    /**
     * Description getResize function
     *
     * @return Resize
     */
    public function getResize(): Resize
    {
        return $this->resize;
    }
}
