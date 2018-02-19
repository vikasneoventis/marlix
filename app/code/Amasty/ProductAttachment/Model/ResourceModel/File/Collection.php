<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\File;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null


    ) {
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection,
            $resource
        );
        $this->collectionFactory = $collectionFactory;
        $this->_setIdFieldName('id');
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\ProductAttachment\Model\File',
            'Amasty\ProductAttachment\Model\ResourceModel\File'
        );
    }

    public function getFilesAdminByProductId($productId, $storeId)
    {
        $this->getFilesAdmin($storeId)->getSelect()
            ->where('main_table.product_id = ?', $productId);
        return $this;
    }

    public function getFilesAdminByProductIds($productIds, $storeId)
    {
        $this->getFilesAdmin($storeId)->getSelect()
            ->where('main_table.product_id IN (?)', $productIds);

        return $this;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    protected function getFilesAdmin($storeId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                ['d' => $this->getTable('amasty_file_store')],
                'main_table.id = d.file_id AND d.store_id = 0',
                ['file_id' => 'main_table.id', '*']
            )
            ->joinLeft(
                ['s' => $this->getTable('amasty_file_store')],
                $joinCondition,
                [
                    'label'                       => $this->getCheckSql(
                        "s.label = '' OR s.label IS NUll", 'd.label',
                        's.label'
                    ),
                    'is_visible'                  => $this->getCheckSql(
                        "s.is_visible = '-1' OR s.is_visible IS NUll",
                        'd.is_visible', 's.is_visible'
                    ),
                    'show_for_ordered'            => $this->getCheckSql(
                        "s.show_for_ordered = '-1' OR s.show_for_ordered IS NUll",
                        'd.show_for_ordered', 's.show_for_ordered'
                    ),
                    'position'                    => $this->getCheckSql(
                        "s.position = '' OR s.position IS NUll",
                        'd.position',
                        's.position'
                    ),
                    'label_is_default'            => $this->getCheckSql(
                        "s.label = '' OR s.label IS NUll", '1', '0'
                    ),
                    'is_visible_is_default'       => $this->getCheckSql(
                        "s.is_visible = '-1' OR s.is_visible IS NUll",
                        '1', '0'
                    ),
                    'show_for_ordered_is_default' => $this->getCheckSql(
                        "s.show_for_ordered = '-1' OR s.show_for_ordered IS NUll",
                        '1', '0'
                    ),
                    'customer_group_is_default'   => $this->getCheckSql(
                        "s.customer_group_is_default IS NULL",
                        '1', 's.customer_group_is_default'
                    ),
                ]
            )
            ->joinLeft(
                ['cg' => $this->getTable(
                    'amasty_file_customer_group'
                )],
                'cg.file_id = main_table.id
                AND (
                    (
                        (s.store_id IS NULL OR s.customer_group_is_default = 1)
                        AND d.store_id = cg.store_id
                    )
                    OR s.store_id = cg.store_id
                )',
                ['customer_groups' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(`cg`.`customer_group_id`)'
                )]
            )
            ->group('main_table.id')
            ->order('s.position ASC');

        return $this;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $customerId
     * @param int $customerGroupId
     *
     * @return $this
     */
    public function getFilesFrontend($productId, $storeId, $customerId, $customerGroupId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                ['d' => $this->getTable('amasty_file_store')],
                "main_table.id = d.file_id AND d.store_id = 0"
            )
            ->joinLeft(
                ['s' => $this->getTable('amasty_file_store')], $joinCondition,
                [
                    'label'            => $this->getCheckSql(
                        "(s.label IS NULL OR s.label = '')", 'd.label',
                        's.label'
                    ),
                    'is_visible'       => $this->getCheckSql(
                        "(s.is_visible IS NULL OR s.is_visible = '-1')",
                        'd.is_visible', 's.is_visible'
                    ),
                    'position'         => $this->getCheckSql(
                        "(s.position IS NULL OR s.position = '')", 'd.position',
                        's.position'
                    ),
                    'show_for_ordered' => $this->getCheckSql(
                        "(s.show_for_ordered IS NULL OR s.show_for_ordered = '-1')",
                        'd.show_for_ordered', 's.show_for_ordered'
                    )
                ]
            )
            ->joinLeft(
                ['cg' => $this->getTable('amasty_file_customer_group')],
                'cg.file_id = main_table.id
                AND (
                    (
                        (s.store_id IS NULL OR s.customer_group_is_default = 1)
                        AND d.store_id = cg.store_id
                    )
                    OR s.store_id = cg.store_id
                )',''
            )
            ->where(
                "(cg.id IS NULL OR cg.customer_group_id = ?)", $customerGroupId
            )
            ->where('main_table.product_id = ?', $productId)
            ->where("((s.is_visible IS NULL OR s.is_visible = '-1') AND d.is_visible = 1) OR (s.is_visible = '1')")
            ->order("position ASC")
            ->group('main_table.id');

        $productIdRow = $this->getCustomerOrderedProduct($productId, $customerId);
        // $select->having(
        //     '(( show_for_ordered = 1 AND main_table.product_id IN (?)) OR show_for_ordered = 0)',
        //     $productIdRow
        // );

        return $this;
    }

    public function getFileFrontend($productId, $storeId, $customerId, $customerGroupId, $fileId)
    {
        $this->getFilesFrontend($productId, $storeId, $customerId, $customerGroupId, $fileId);
        $this->getSelect()->where('main_table.id = ?', $fileId);

        return $this;
    }

    public function loadByIdAndCustomerGroupIdAndOrdered($productId, $storeId, $customerId, $customerGroupId, $fileId)
    {
        $this->getFileFrontend($productId, $storeId, $customerId, $customerGroupId, $fileId);
        return $this->getFirstItem();

    }

    public function getCustomerOrderedProduct($productId, $customerId) {

        $salesCollection = $this->collectionFactory->create();
        $salesCollection
            ->join(array('oi' => $this->getTable('sales_order_item')), sprintf('main_table.entity_id = oi.order_id AND product_id = %d', $productId), 'product_id')
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->addFieldToFilter('main_table.status', \Magento\Sales\Model\Order::STATE_COMPLETE);
        $salesCollection->distinct(true);
        return $salesCollection->getColumnValues('product_id');
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new \Zend_Db_Expr($expression);
    }

    public function addProducts()
    {
        $this->getSelect()->joinLeft(
            ['product' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = product.entity_id'
        );
        return $this;
    }
}
