<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoAll\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * This class was created for avoid magento bug:
 * @see https://github.com/magento/magento2/issues/6076
 */
class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    /**
     * @var \MageWorx\SeoAll\Helper\LinkFieldResolver
     */
    protected $linkFieldResolver;

    /**
     * Category constructor.
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \MageWorx\SeoAll\Helper\LinkFieldResolver $linkFieldResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageWorx\SeoAll\Helper\LinkFieldResolver $linkFieldResolver
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $eventManager, $categoryTreeFactory, $categoryCollectionFactory);
        $this->linkFieldResolver = $linkFieldResolver;
    }

    /**
     * Avoid magento bug related to getRawAttributeValue()
     *
     * @see https://github.com/magento/magento2/issues/6076
     * @param string $alias
     * @return string
     */
    public function getTable($alias)
    {
        if ($alias == 'catalog_product_entity') {
            $alias = $this->getEntityTable();
        }
        return parent::getTable($alias);
    }

    /**
     * @return string
     */
    public function getLinkField()
    {
        return $this->linkFieldResolver->getLinkField(CategoryInterface::class, 'entity_id');
    }
}
