<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel;

use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\OptionTemplates\Model\Group as GroupModel;

class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     * @var string
     */
    protected $productRelationTable = 'mageworx_optiontemplates_relation';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        LibDateTime $dateTime,
        ManagerInterface $eventManager
    ) {
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->productFactory = $productFactory;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_optiontemplates_group', 'group_id');
    }

    /**
     * Retrieve default values for create
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'assign_type' => \MageWorx\OptionTemplates\Model\Group\Source\AssignType::ASSIGN_BY_GRID,
        ];
    }

    /**
     * Before save callback
     *
     * @param AbstractModel|\MageWorx\OptionTemplates\Model\Group $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     *
     * @param int $groupId
     * @return array
     */
    public function getGroupOptionIdsByGroupId($groupId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                []
            )
            ->join(
                ['group_option_table' => $this->getTable('mageworx_optiontemplates_group_option')],
                'main_table.group_id = group_option_table.group_id',
                ['option_id']
            )
            ->where('main_table.group_id = ?', $groupId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * After save callback
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setProductRelation();

        return parent::_afterSave($object);
    }

    /**
     * Clear all template relations
     *
     * @param \MageWorx\OptionTemplates\Model\Group $object
     * @return $this
     */
    public function clearProductRelation(\MageWorx\OptionTemplates\Model\Group $object)
    {
        $id = $object->getId();
        $condition = ['group_id=?' => $id];
        $this->getConnection()->delete($this->getTable($this->productRelationTable), $condition);
        $object->setIsChangedProductList(true);

        return $this;
    }

    /**
     * @param GroupModel $group
     * @return array
     */
    public function getProducts(GroupModel $group)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->productRelationTable),
            ['product_id']
        )
            ->where(
                'group_id = :group_id'
            );
        $bind = ['group_id' => (int)$group->getId()];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * Add group product relation by group Id
     *
     * @param int $groupId
     * @param int $productId
     * @return int|null
     */
    public function addProductRelation($groupId, $productId)
    {
        if ($productId && $groupId) {
            $adapter = $this->getConnection();

            $data = [
                'group_id' => (int)$groupId,
                'product_id' => (int)$productId,
            ];

            return $adapter->insert($this->getTable($this->productRelationTable), $data);
        }

        return null;
    }

    /**
     * Delete group product relation by group ID
     *
     * @param int $groupId
     * @param int $productId
     * @return int|null
     */
    public function deleteProductRelation($groupId, $productId)
    {
        if (!empty($productId) && $groupId) {
            $adapter = $this->getConnection();
            $condition = ['product_id IN(?)' => (int)$productId, 'group_id=?' => (int)$groupId];

            return $adapter->delete($this->getTable($this->productRelationTable), $condition);
        }

        return null;
    }
}
