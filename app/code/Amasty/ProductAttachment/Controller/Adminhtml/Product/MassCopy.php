<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassCopy extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {

            $parentProductId = $this->getRequest()->getParam('amasty_file_field');
            if (!$parentProductId) {
                //compatibility with Amasty Mass Product Actions
                $parentProductId = $this->getRequest()->getParam('amasty_paction_field');
            }
            $this->copyAttachment($collection->getAllIds(), $parentProductId);

            $this->messageManager->addSuccess(
                __('Attachments for %1 selected record(s) have been copied.', $collection->getSize())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }

    protected function copyAttachment($productIds, $parentProductId)
    {

        if (!$parentProductId) {
            throw new LocalizedException(__('Please provide the value for the action'));
        }
        $parentProductId = +$parentProductId;

        if (!$parentProductId) {
            throw new LocalizedException(__('Please provide the correct value for the action'));
        }

        /**
         * @var \Magento\Catalog\Model\Product $productModel
         */
        $productModel = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $productModel->load($parentProductId);
        if (!$productModel->getId()) {
            throw new LocalizedException(__('Product with ID:%1 doesn\'t exist', $parentProductId));
        }

        /**
         * @var \Amasty\ProductAttachment\Model\ResourceModel\File\Collection $filesCollection
         */
        $filesCollection = $this->_objectManager->create('Amasty\ProductAttachment\Model\ResourceModel\File\Collection');
        $filesCollection->addFieldToFilter('product_id', $parentProductId);

        if ($filesCollection->getSize() <= 0) {
            throw new LocalizedException(__('Product with ID:%1 has no attachments', $parentProductId));
        }

        /**
         * @var \Amasty\ProductAttachment\Model\File $file
         */
        foreach ($filesCollection as $file)
        {
            /**
             * @var \Amasty\ProductAttachment\Model\ResourceModel\File\Store\Collection $storeValues
             */
            $storeValues = $this->_objectManager->create('Amasty\ProductAttachment\Model\ResourceModel\File\Store\Collection');
            $storeValues->addFieldToFilter('file_id', $file->getId());

            /**
             * @var \Amasty\ProductAttachment\Model\ResourceModel\File\CustomerGroup\Collection $storeValues
             */
            $customerGroupValues = $this->_objectManager->create('Amasty\ProductAttachment\Model\ResourceModel\File\CustomerGroup\Collection');
            $customerGroupValues->addFieldToFilter('file_id', $file->getId());

            foreach ($productIds as $product)
            {
                $file
                    ->unsId()
                    ->setProductId($product)
                    ->save();

                foreach ($storeValues as $store)
                {
                    $store
                        ->unsId()
                        ->setFileId($file->getId())
                        ->save();
                }
                foreach ($customerGroupValues as $groupValue) {
                    $groupValue
                        ->unsId()
                        ->setFileId($file->getId())
                        ->save();
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ProductAttachment::copy');
    }

}
