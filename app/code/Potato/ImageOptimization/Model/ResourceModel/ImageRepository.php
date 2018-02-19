<?php

namespace Potato\ImageOptimization\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\ImageOptimization\Api as ImageApi;
use Potato\ImageOptimization\Model as ImageModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class ImageRepository
 */
class ImageRepository implements ImageApi\ImageRepositoryInterface
{
    const PROCESS_OPTIMIZATION_IMAGE_LIMIT = 25;
    /**
     * @var ImageModel\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var ImageModel\ImageRegistry
     */
    protected $imageRegistry;

    /**
     * @var ImageApi\Data\ImageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Image
     */
    protected $imageResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchBuilder;

    /**
     * ImageRepository constructor.
     * @param ImageModel\ImageFactory $imageFactory
     * @param ImageModel\ImageRegistry $imageRegistry
     * @param ImageApi\Data\ImageSearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Image $imageResource
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        ImageModel\ImageFactory $imageFactory,
        ImageModel\ImageRegistry $imageRegistry,
        ImageApi\Data\ImageSearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Image $imageResource,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->imageFactory = $imageFactory;
        $this->imageRegistry = $imageRegistry;
        $this->imageResource = $imageResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * Create new empty image model
     * @return ImageApi\Data\ImageInterface
     */
    public function create()
    {
        return $this->imageRegistry->create();
    }
    
    /**
     * @param ImageApi\Data\ImageInterface $image
     * @return ImageApi\Data\ImageInterface
     * @throws \Exception
     */
    public function save(ImageApi\Data\ImageInterface $image)
    {
        $imageData = $this->extensibleDataObjectConverter->toNestedArray(
            $image,
            [],
            ImageApi\Data\ImageInterface::class
        );
        $imageModel = $this->imageFactory->create();
        $imageModel->addData($imageData);
        $imageModel->setId($image->getId());
        $this->imageResource->save($imageModel);
        $this->imageRegistry->push($imageModel);
        $savedObject = $this->get($imageModel->getId());
        return $savedObject;
    }

    /**
     * @param int $imageId
     *
     * @return ImageApi\Data\ImageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($imageId)
    {
        $imageModel = $this->imageRegistry->retrieve($imageId);
        return $imageModel->getDataModel();
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getByPath($path)
    {
        $imageModel = $this->imageRegistry->retrieveByPath($path);
        return $imageModel->getDataModel();
    }

    /**
     * @param ImageApi\Data\ImageInterface $image
     * @return bool
     */
    public function delete(ImageApi\Data\ImageInterface $image)
    {
        return $this->deleteById($image->getId());
    }

    /**
     * @param int $imageId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($imageId)
    {
        $imageModel = $this->imageRegistry->retrieve($imageId);
        $imageModel->getResource()->delete($imageModel);
        $this->imageRegistry->remove($imageId);
        return true;
    }

    /**
     * @param Api\SearchCriteriaInterface $searchCriteria
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getList(Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->imageFactory->create()->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
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
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var Api\SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $images = [];
        foreach ($collection as $imageModel) {
            $images[] = $imageModel->getDataModel();
        }
        $searchResults->setItems($images);
        return $searchResults;
    }

    /**
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getAllList()
    {
        $criteria = $this->searchBuilder->create();
        return $this->getList($criteria);
    }

    /**
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getNeedToOptimizationList()
    {
        $criteria = $this
            ->searchBuilder
            ->addFilter(
                'status',
                [StatusSource::STATUS_PENDING , StatusSource::STATUS_OUTDATED],
                'in'
            )
            ->setPageSize(self::PROCESS_OPTIMIZATION_IMAGE_LIMIT)
            ->create();
        return $this->getList($criteria);
    }

    /**
     * @param string $status
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getListByStatus($status)
    {
        $criteria = $this
            ->searchBuilder
            ->addFilter(
                'status',
                $status,
                'eq'
            )
            ->create();
        return $this->getList($criteria);
    }
}
