<?php

declare(strict_types=1);

namespace Web200\ImageResize\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Cache as MagentoAdminCache;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Web200\ImageResize\Model\Cache as ResizeCache;

/**
 * Class CleanResizedImages
 *
 * @package   Web200\ImageResize\Controller\Adminhtml\Cache
 * @author    Web200 <contact@web200.fr>
 * @copyright 2019 Web200
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.web200.fr/
 */
class CleanResizedImages extends MagentoAdminCache
{
    /**
     * @var ResizeCache
     */
    protected $resizeCache;

    /**
     * CleanResizedImages constructor.
     *
     * @param ResizeCache       $resizeCache
     * @param Context           $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface    $cacheState
     * @param Pool              $cacheFrontendPool
     * @param PageFactory       $resultPageFactory
     */
    public function __construct(
        ResizeCache $resizeCache,
        Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $cacheTypeList, $cacheState, $cacheFrontendPool, $resultPageFactory);

        $this->resizeCache = $resizeCache;
    }

    /**
     * Clean JS/css files cache
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $this->resizerCache->clearResizedImagesCache();
            $this->_eventManager->dispatch('web200_imageresize_clean_images_cache_after');
            $this->messageManager->addSuccessMessage(__('The resized images cache was cleaned.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('An error occurred while clearing the resized images cache.')
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('adminhtml/cache');
    }
}
