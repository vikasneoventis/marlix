<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework;
use Potato\ImageOptimization\Api\Data;

/**
 * Class Image
 * @package Potato\ImageOptimization\Model
 */
class Image extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Potato\ImageOptimization\Api\Data\ImageInterfaceFactory
     */
    private $imageDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Image constructor.
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Image $resource
     * @param ResourceModel\Image\Collection $resourceCollection
     * @param Data\ImageInterfaceFactory $imageDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Image $resource,
        ResourceModel\Image\Collection $resourceCollection,
        Data\ImageInterfaceFactory $imageDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->imageDataFactory = $imageDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Image::class);
    }
    
    /**
     * Retrieve Image model with data
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->imageDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\ImageInterface::class
        );
        return $dataObject;
    }
}
