<?php

declare(strict_types=1);

namespace Web200\ImageResize\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Web200\ImageResize\Model\Resize;

/**
 * Class ImageResize
 *
 * @package   Web200\ImageResize\Helper
 * @author    Web200 <contact@web200.fr>
 * @copyright 2020 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class ImageResize extends AbstractHelper
{
    /**
     * Resize
     *
     * @var Resize $resize
     */
    protected $resize;

    /**
     * ImageResize constructor.
     *
     * @param Resize  $resize
     * @param Context $context
     */
    public function __construct(
        Resize $resize,
        Context $context
    ) {
        parent::__construct($context);

        $this->resize  = $resize;
    }

    /**
     * Get resize
     *
     * @return Resize
     */
    public function getResize(): Resize
    {
        return $this->resize;
    }
}
