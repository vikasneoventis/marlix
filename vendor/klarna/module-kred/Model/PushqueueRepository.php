<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model;

use Klarna\Kred\Api\PushqueueInterface;
use Klarna\Kred\Api\PushqueueRepositoryInterface;
use Klarna\Kred\Model\ResourceModel\Pushqueue as PushqueueResource;
use Klarna\Kred\Model\ResourceModel\Pushqueue\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PushqueueRepository
 *
 * @package Klarna\Kred\Model
 */
class PushqueueRepository implements PushqueueRepositoryInterface
{
    /**
     * @var PushqueueFactory
     */
    protected $pushqueueFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var PushqueueResource
     */
    protected $resourceModel;

    /**
     * OrderRepository constructor.
     *
     * @param PushqueueFactory              $pushqueueFactory
     * @param PushqueueResource             $resourceModel
     * @param CollectionFactory             $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        PushqueueFactory $pushqueueFactory,
        PushqueueResource $resourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->pushqueueFactory = $pushqueueFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param PushqueueInterface $pushqueue
     * @return PushqueueInterface
     * @throws CouldNotSaveException
     */
    public function save(PushqueueInterface $pushqueue)
    {
        try {
            $pushqueue->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $pushqueue;
    }

    /**
     * Load by checkout id
     *
     * @param string $checkoutId
     * @return PushqueueInterface
     * @throws NoSuchEntityException
     */
    public function getByCheckoutId($checkoutId)
    {
        $pushqueue = $this->pushqueueFactory->create();

        $pushqueueId = $this->resourceModel->getIdByCheckoutId($checkoutId);
        if (!$pushqueueId) {
            $pushqueue->setKlarnaCheckoutId($checkoutId);
            return $pushqueue;
        }
        $pushqueue->load($pushqueueId);
        return $pushqueue;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param PushqueueInterface $pushqueue
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PushqueueInterface $pushqueue)
    {
        try {
            $pushqueue->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $pushqueue = $this->objectFactory->create();
        $pushqueue->load($id);
        if (!$pushqueue->getId()) {
            throw new NoSuchEntityException(__('Pushqueue with id "%1" does not exist.', $id));
        }
        return $pushqueue;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $pushqueues = [];
        foreach ($collection as $pushqueueModel) {
            $pushqueues[] = $pushqueueModel;
        }
        $searchResults->setItems($pushqueues);
        return $searchResults;
    }
}
