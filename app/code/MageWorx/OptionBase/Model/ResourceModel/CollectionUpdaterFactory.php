<?php

namespace MageWorx\OptionBase\Model\ResourceModel;

class CollectionUpdaterFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Mapper for collection updater
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $map = [
        'MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\Collection' => [
            'instance' => 'MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater\Option'
        ],
        'MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\Value\Collection' => [
            'instance' => 'MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater\Value'
        ],
        'Magento\Catalog\Model\ResourceModel\Product\Option\Collection' => [
            'instance' => 'MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater\Option'
        ],
        'Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection' => [
            'instance' => 'MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater\Value'
        ]
    ];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param mixed $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    public function create($collection)
    {
        $collectionClassName = str_replace('\Interceptor', '', get_class($collection));

        $data = $this->getDataByClass($collectionClassName);

        $instance = $data['instance'];

        return $this->objectManager->create(
            $instance,
            [
                'collection' => $collection
            ]
        );
    }

    /**
     * Retrieve data to load corresponding class
     * for update Option collection or Value collection.
     *
     * @param string $class
     * @return array
     */
    protected function getDataByClass($class)
    {
        if (!isset($this->map[$class])) {
            if (strpos($class, '\Value\\') === false) {
                return $this->map['Magento\Catalog\Model\ResourceModel\Product\Option\Collection'];
            }

            return $this->map['Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection'];
        }

        return $this->map[$class];
    }
}
