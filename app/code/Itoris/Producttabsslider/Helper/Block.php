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

namespace Itoris\Producttabsslider\Helper;


class Block extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'helper_product_tabs';
    protected $objectManager;
    protected $_filterProvider;

    public function getHtml($filter,$id,$store){
        $this->_filterProvider=$filter;
        $html=[];
        $this->objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $storeId = $store->getStore()->getId();
        $idProduct = $id;
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        $customer =  $this->objectManager->create('Magento\Customer\Model\Session')->getCustomer();
        $filter = $this->objectManager->create('Magento\Framework\Filter\Template');

        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($idProduct);
        if ($product->getId()) $attributes = $this->getAttributeData($product); else $attributes = [];

        $tabForm = $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        $tabForm->getSelect()->reset();

        $tabForm->getSelect()->from(
            new \Zend_Db_Expr
            ("(SELECT tt.* FROM (SELECT `main_table`.*, `iptv1`.`value` AS `label`, `iptvi2`.`value` AS `status`,`iptvi3`.`value` AS `order`, `iptvi4`.`value` AS `content`, 
            `iptvi5`.`value` AS `show_purchased`, `iptvi6`.`value` AS `group`,cg.customer_group_code AS `groupname` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_varchar')}` AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                 INNER JOIN `{$resource->getTableName('customer_group')}` AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                 WHERE ((iptv1.product_id IS NULL OR iptv1.product_id={$idProduct}) AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id={$idProduct}) AND (iptvi5.product_id IS NULL OR iptvi5.product_id={$idProduct})
                  AND (iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND (iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND (iptvi4.product_id IS NULL OR iptvi4.product_id={$idProduct}) 
                  AND  (iptvi6.product_id IS NULL OR iptvi6.product_id={$idProduct})
                 AND ((iptv1.store_id  IS NULL OR iptv1.store_id={$storeId}) AND (iptvi2.store_id IS NULL OR iptvi2.store_id={$storeId}) AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$storeId}) 
                 AND (iptvi5.store_id IS NULL OR iptvi5.store_id={$storeId})  AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$storeId})  AND (iptvi4.store_id IS NULL OR iptvi4.store_id={$storeId}) 
                 AND (iptvi6.store_id IS NULL OR iptvi6.store_id={$storeId}))) 
                 HAVING 1  
                 ORDER BY iptv1.value_id DESC ,iptvi3.value_id DESC,iptvi6.value_id DESC,iptvi2.value_id DESC,iptvi4.value_id DESC,iptvi5.value_id DESC ) as tt  
                 GROUP BY tt.tab_id)
          "))->where('status>0')->group('t.tab_id')->order('order');

        $customer =  $this->objectManager->create('Magento\Customer\Model\Session')->getCustomer();
        $filter = $this->objectManager->create('Magento\Framework\Filter\Template');

        if ($customer->getId()) {

            $showPupchased[]=1;
            $groupId = $customer->getGroupId();
            foreach($tabForm->getData() as $dp) {
                $dp['content'] = $this->replaceAttributes($dp['content'], $attributes);
                if (strtolower(strip_tags($dp['content'])) == 'no data') {
                    continue;
                }
                $groups = explode(',', $dp['group']);
                if ((in_array($groupId, $groups) || in_array(-1, $groups)) && in_array($dp['show_purchased'],$showPupchased)) {
                    $html[$dp['label']] = $this->_filterProvider->getBlockFilter()->filter($dp['content']);
                }

            }
            return $html;

        }else{
            $showPupchased[]=1;
            $groupId=0;
            foreach($tabForm->getData() as $dp) {
                $dp['content'] = $this->replaceAttributes($dp['content'], $attributes);
                if (strtolower(strip_tags($dp['content'])) == 'no data') {
                    continue;
                }
                $groups = explode(',', $dp['group']);
                if ((in_array($groupId, $groups) || in_array(-1, $groups)) && in_array($dp['show_purchased'],$showPupchased)) {
                    $filter->filter($dp['content']);
                    $html[$dp['label']] = $this->_filterProvider->getBlockFilter()->filter($dp['content']);
                }
            }
            return $html;
        }
    }

    public function replaceAttributes($content, $attributes) {
        foreach($attributes as $code => $value) {
            $content = str_ireplace($code, $value, $content);
        }
        return $content;
    }

    protected function getAttributeData($product) {
        $attributes = $product->getAttributes();
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeCodeStr = '{{' . $attributeCode . '}}';
            $options = $attribute->getOptions();
            $value = $product->getData($attributeCode);
            if (!empty($options)) foreach($options as $option) {
                if ((int)$option['value'] == (int)$value) {
                    $value = $option['label'];
                    break;
                }
            }
            if (is_string($value)) $attributeCodes[$attributeCodeStr] = $value;

        }
        return $attributeCodes;
    }
}