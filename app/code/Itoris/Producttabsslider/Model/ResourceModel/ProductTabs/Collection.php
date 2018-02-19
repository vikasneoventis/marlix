<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Model\ResourceModel\ProductTabs;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'tab_id';
    protected $_alias='main_table';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Itoris\Producttabsslider\Model\ProductTabs', 'Itoris\Producttabsslider\Model\ResourceModel\ProductTabs');
    }
    public function setAllias($alias){
        $this->_alias=$alias;
    }
    public function  resetSelect(){
       $this->_reset();
    }
    public function addItem(\Magento\Framework\DataObject $item)
    {
        $itemId = $this->_getItemId($item);

        if ($itemId !== null) {
            if (isset($this->_items[$itemId])) {
                $count =  count($this->_items);
                $this->_items[($count+1)] = $item;

            }else {
                $this->_items[$itemId] = $item;
            }
        } else {
            $this->_addItem($item);
        }
        return $this;
    }
    public function getSelectCountSqlCustom()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->columns($this->_alias.'.tab_id');

        return $countSelect;
    }
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $idsSelect->columns($this->getResource()->getIdFieldName(), $this->_alias);
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
    public function getSizeTabs($bool=false)
    {

                    $sql = $this->getSelectCountSqlCustom();
                    $total = $this->getConnection()->fetchAll($sql, $this->_bindParams);
                    $total = count($total);
                    $this->_totalRecords = $total;
            return intval($this->_totalRecords);
        }

}