<?php

namespace BoostMyShop\BarcodeLabel\Model\ResourceModel;


class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {

    }

    public function getLastSimpleProductId()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('catalog_product_entity'), array(new \Zend_Db_Expr('MAX(entity_id) as product_id')))
            ->where('type_id = "simple"');
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }
}
