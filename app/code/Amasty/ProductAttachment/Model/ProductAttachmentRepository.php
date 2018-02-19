<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Model;


use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\ProductAttachment\Api\Data;
use Magento\Framework\Api\SortOrder;

class ProductAttachmentRepository implements \Amasty\ProductAttachment\Api\ProductAttachmentRepositoryInterface
{
    /**
     * @var ResourceModel\File
     */
    protected $resource;

    /**
     * @var ProductAttachmentFactory
     */
    protected $productAttachmentFactory;

    /**
     * @var Data\ProductAttachmentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceModel\File\CollectionFactory
     */
    protected $productAttachmentCollectionFactory;

    /**
     * @var Data\ProductAttachmentInterfaceFactory
     */
    protected $dataProductAttachmentFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    protected $dataObjectProcessor;

    public function __construct(
        \Amasty\ProductAttachment\Model\ResourceModel\File $resource,
        \Amasty\ProductAttachment\Model\ProductAttachmentFactory $productAttachmentFactory,
        Data\ProductAttachmentSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\ProductAttachment\Model\ResourceModel\File\CollectionFactory $productAttachmentCollectionFactory,
        \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterfaceFactory $dataProductAttachmentFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->productAttachmentFactory = $productAttachmentFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->productAttachmentCollectionFactory = $productAttachmentCollectionFactory;
        $this->dataProductAttachmentFactory = $dataProductAttachmentFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;

    }


    public function save(Data\ProductAttachmentInterface $productAttachment)
    {
        try {
            $this->resource->save($productAttachment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $productAttachment;
    }

    public function saveExist(Data\ProductAttachmentInterface $productAttachment)
    {
        $attach = $this->getById($productAttachment->getId());
        $attach->afterLoad();
        foreach ($attach->getData() as $key=>$value) {
            $productAttachment->setOrigData($key, $value);
        }
        return $this->save($productAttachment);
    }

    public function getById($attachmentId)
    {
        $productAttachment = $this->productAttachmentFactory->create();
        $this->resource->load($productAttachment, $attachmentId);
        if (!$productAttachment->getId()) {
            throw new NoSuchEntityException(__('Product attachment with id "%1" does not exist.', $attachmentId));
        }
        return $productAttachment;
    }

    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->productAttachmentCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                /*if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }*/
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $productAttachments = [];
        /** @var File $productAttachmentModel */
        foreach ($collection as $productAttachmentModel) {
            $productAttachmentData = $this->dataProductAttachmentFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productAttachmentData,
                $productAttachmentModel->getData(),
                'Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface'
            );
            $productAttachments[] = $this->dataObjectProcessor->buildOutputDataArray(
                $productAttachmentData,
                'Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface'
            );
        }
        $searchResults->setItems($productAttachments);
        return $searchResults;
    }

    public function delete(Data\ProductAttachmentInterface $productAttachment)
    {
        try {
            $this->resource->delete($productAttachment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($attachmentId)
    {
        return $this->delete($this->getById($attachmentId));
    }
}
