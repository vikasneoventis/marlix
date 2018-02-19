<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @param Builder $productBuilder
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param Context $context
     */
    public function __construct(
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {
            $this->deleteAttachmentForProducts($collection->getAllIds());
            $this->messageManager->addSuccess(
                __('Attachments for %1 selected record(s) have been deleted.', $collection->getSize())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }

    protected function deleteAttachmentForProducts($productIds)
    {
        /**
         * @var \Amasty\ProductAttachment\Model\ResourceModel\File\Collection $filesCollection
         */
        $filesCollection = $this->_objectManager->create('Amasty\ProductAttachment\Model\ResourceModel\File\Collection');

        $filesCollection->addFieldToFilter('product_id', $productIds);
        $filesCollection->walk('delete');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ProductAttachment::delete');
    }
}
