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

namespace Itoris\Producttabsslider\Block\Adminhtml\Producttabs\FormEdit;

/**
 * Adminhtml blog post edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /** @var \Magento\Framework\Data\Form $form */
    protected $objectManager;
    protected $_template = 'Itoris_Producttabsslider::widget/form.phtml';

    protected function _prepareForm()
    {
		
		if($_SERVER['REMOTE_ADDR']=='178.121.205.85'){   \Zend_Debug::dump(            'FFFFFFFFFFFFFFF'                 );die;   }
		
		
        $tab_id = $this->getRequest()->getParam('id');
        $prod_id = $this->getRequest()->getParam('prod_id');

        $storeId = $this->getRequest()->getParam('store');
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $this->_coreRegistry->registry('producttabsslider_productTabs');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->objectManager->create('Itoris\Producttabsslider\Block\Adminhtml\FactoryForm\FactoryForm')->create(
            ['data' => ['id' => 'edit_form', 'class' => 'itoris_producttabs_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $soreAttribute = '';
        $storeManager = $this->objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        //      $resource->getConnection()->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
        $id = $storeManager->getStore()->getId();
        $collection = $this->objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection');
        $wysig = $this->objectManager->create('Magento\Framework\DataObject');
        $tabForm = $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        if ($tab_id != NULL && $storeId == Null && $prod_id == NULL) {
            $tabForm->getSelect()
                ->join("{$resource->getTableName('itoris_product_tabs_value_varchar')} as iptv1", "main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1", ['label' => 'iptv1.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi2", "main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2", ['is_active' => 'iptvi2.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi4", "main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4", ['content' => 'iptvi4.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi3", "main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3", ['order' => 'iptvi3.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi5", "main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5", ['show_purchased' => 'iptvi5.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi6", "main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6", ['group' => 'iptvi6.value'])
                ->join("{$resource->getTableName('customer_group')} as cg", "find_in_set(cg.customer_group_id,iptvi6.value)", ['group_name' => 'GROUP_CONCAT(DISTINCT cg.customer_group_code SEPARATOR \', \')'])
                ->where('iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL
             AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND iptvi6.product_id IS NULL AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL
             AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL AND  main_table.tab_id = ' . $tab_id);
        } elseif ($tab_id != NULL && $prod_id == NULL && $storeId != NULL) {
			
			if($_SERVER['REMOTE_ADDR']=='178.121.205.85'){   \Zend_Debug::dump(            'FFFFFFFFFFFFFFF'                 );die;   }
			
			
            $tabForm->getSelect()
                ->join("{$resource->getTableName('itoris_product_tabs_value_varchar')} as iptv1", "main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1", ['label' => 'iptv1.value', 'storeAttr' => 'iptv1.store_id'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi2", "main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2", ['is_active' => 'iptvi2.value', 'storeAttr2' => 'iptvi2.store_id'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi4", "main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4", ['content' => 'iptvi4.value', 'storeAttr4' => 'iptvi4.store_id'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi3", "main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3", ['order' => 'iptvi3.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi5", "main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5", ['show_purchased' => 'iptvi5.value', 'storeAttr5' => 'iptvi5.store_id'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi6", "main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6", ['group' => 'iptvi6.value', 'storeAttr6' => 'iptvi6.store_id'])
                /// ->join('customer_group as cg',"find_in_set(cg.customer_group_id,iptvi6.value)",['group_name'=>'GROUP_CONCAT(DISTINCT cg.customer_group_code SEPARATOR \', \')'])
                ->where("iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND
 iptvi6.product_id IS NULL AND (iptv1.store_id IS NULL OR iptv1.store_id={$storeId}) AND (iptvi2.store_id IS NULL OR  iptvi2.store_id={$storeId})AND (iptvi5.store_id IS NULL OR  iptvi5.store_id={$storeId})
 AND (iptvi4.store_id  IS NULL OR iptvi4.store_id={$storeId}) AND (iptvi6.store_id IS NULL OR iptvi6.store_id={$storeId}) AND  main_table.tab_id = " . $tab_id)
                ->order(['iptv1.store_id DESC', 'iptvi4.store_id DESC', 'iptvi2.store_id DESC', 'iptvi5.store_id DESC', 'iptvi6.store_id DESC'])->limit(1);


        } elseif ($tab_id != NULL && $prod_id != NULL && $storeId == NULL) {
//  ,`iptv1`.store_id as storeAttr,iptvi2`.store_id as storeAttr2,iptvi5`.store_id as storeAttr5,iptvi4`.store_id as storeAttr4,iptvi6`.store_id as storeAttr6,
            $tabForm = $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
            $tabForm->getSelect()->reset();

            $tabForm->getSelect()->from(['main_table' => $resource->getTableName('itoris_producttabs_tabs')] )->
            join(['iptv1' => $resource->getTableName('itoris_product_tabs_value_varchar')], ' main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1 ', ['prodAttr'=>'iptv1.product_id', 'label'=>'iptv1.value'])->
            join(['iptvi2' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2 ', ['prodAttr2'=>'iptvi2.product_id', 'is_active'=>'iptvi2.value'])->
            join(['iptvi4' => $resource->getTableName('itoris_product_tabs_value_text')], ' main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4 ', ['prodAttr4'=>'iptvi4.product_id', 'content'=>'iptvi4.value'])->
            join(['iptvi3' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3 ', ['order'=>'iptvi3.value'])->
            join(['iptvi5' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5 ', ['prodAttr5'=>'iptvi5.product_id', 'show_purchased'=>'iptvi5.value'])->
            join(['iptvi6' => $resource->getTableName('itoris_product_tabs_value_text')], ' main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6 ', ['prodAttr6'=>'iptvi6.product_id', 'group'=>'iptvi6.value'])->
            join(['cg' => $resource->getTableName('customer_group')], ' find_in_set(cg.customer_group_id,iptvi6.value) ', ['groupname'=>'cg.customer_group_code'])->

        /* where('(( iptv1.product_id IN(NULL, '.$prod_id.') ) AND  ( iptvi2.product_id IN(NULL, '.$prod_id.') ) AND ( iptvi5.product_id IN(NULL, '.$prod_id.') ) AND (iptvi3.product_id IN(NULL, '.$prod_id.') ) AND ( iptvi4.product_id IN(NULL, '.$prod_id.') ) AND  ( iptvi6.product_id  IN(NULL, '.$prod_id.') )
                 AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) AND main_table.tab_id='.$tab_id )->*/
          where('((iptv1.product_id IS NULL OR iptv1.product_id='.$prod_id.') AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id='.$prod_id.') AND (iptvi5.product_id IS NULL OR iptvi5.product_id='.$prod_id.') AND (iptvi3.product_id IS NULL OR iptvi3.product_id='.$prod_id.') 
                    AND (iptvi4.product_id IS NULL OR iptvi4.product_id='.$prod_id.') AND  (iptvi6.product_id IS NULL OR iptvi6.product_id='.$prod_id.')
                    AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) AND main_table.tab_id='.$tab_id)->
            order(['iptv1.product_id DESC', 'iptvi2.product_id DESC', 'iptvi3.product_id DESC', 'iptvi4.product_id DESC', 'iptvi5.product_id DESC', 'iptvi6.product_id DESC'])->
            limit(1);

            $arrX = $tabForm->getData()[0];

            $arrX['prodConcatAttr'] = ($arrX['prodAttr'] === null) ? 0 : $arrX['prodAttr'];
            $arrX['prodConcatAttr2'] = ($arrX['prodAttr2'] === null) ? 0 : $arrX['prodAttr2'];
            $arrX['prodConcatAttr4'] = ($arrX['prodAttr4'] === null) ? 0 : $arrX['prodAttr4'];
            $arrX['prodConcatAttr5'] = ($arrX['prodAttr5'] === null) ? 0 : $arrX['prodAttr5'];
            $arrX['prodConcatAttr6'] = ($arrX['prodAttr6'] === null) ? 0 : $arrX['prodAttr6'];
            $arrX['group_name'] = ($arrX['groupname'] === null) ? 0 : $arrX['groupname'];

            $tabForm = $arrX;

        } elseif ($tab_id != NULL && $prod_id != NULL && $storeId != NULL) {
			
			if($_SERVER['REMOTE_ADDR']=='178.121.205.85'){   \Zend_Debug::dump(            'FFFFFFFFFFFFFFF'                 );die;   }
			
            $tabForm = $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
            $tabForm->getSelect()->reset();
		
		$tabForm->getSelect()->from(['main_table' => $resource->getTableName('itoris_producttabs_tabs')] )->
            join(['iptv1' => $resource->getTableName('itoris_product_tabs_value_varchar')], ' main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1 ', ['prodAttr'=>'iptv1.product_id', 'label'=>'iptv1.value', 'storeAttr'=>'iptv1.store_id'])->
            join(['iptvi2' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2 ', ['prodAttr2'=>'iptvi2.product_id', 'is_active'=>'iptvi2.value', 'storeAttr2'=>'iptvi2.store_id'])->
            join(['iptvi4' => $resource->getTableName('itoris_product_tabs_value_text')], ' main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4 ', ['prodAttr4'=>'iptvi4.product_id', 'content'=>'iptvi4.value', 'storeAttr4'=>'iptvi4.store_id'])->
            join(['iptvi3' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3 ', ['order'=>'iptvi3.value'])->
            join(['iptvi5' => $resource->getTableName('itoris_product_tabs_value_int')], ' main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5 ', ['prodAttr5'=>'iptvi5.product_id', 'show_purchased'=>'iptvi5.value', 'storeAttr5'=>'iptvi5.store_id'])->
            join(['iptvi6' => $resource->getTableName('itoris_product_tabs_value_text')], ' main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6 ', ['prodAttr6'=>'iptvi6.product_id', 'group'=>'iptvi6.value', 'storeAttr6'=>'iptvi6.store_id'])->
            join(['cg' => $resource->getTableName('customer_group')], ' find_in_set(cg.customer_group_id,iptvi6.value) ', ['groupname'=>'cg.customer_group_code'])->
            where('((iptv1.product_id IS NULL OR iptv1.product_id='.$prod_id.') AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id='.$prod_id.') AND (iptvi5.product_id IS NULL OR iptvi5.product_id='.$prod_id.') 
                AND (iptvi3.product_id IS NULL OR iptvi3.product_id='.$prod_id.') AND (iptvi4.product_id IS NULL OR iptvi4.product_id='.$prod_id.') AND  (iptvi6.product_id IS NULL OR iptvi6.product_id='.$prod_id.')
                 AND ((iptv1.store_id  IS NULL OR iptv1.store_id='.$storeId.') AND (iptvi2.store_id IS NULL OR iptvi2.store_id='.$storeId.') AND (iptvi5.store_id IS NULL OR iptvi5.store_id='.$storeId.')  
                 AND (iptvi3.store_id IS NULL OR iptvi3.store_id='.$storeId.')  AND (iptvi4.store_id IS NULL OR iptvi4.store_id='.$storeId.') AND (iptvi6.store_id IS NULL OR iptvi6.store_id='.$storeId.'))) AND main_table.tab_id='.$tab_id)->
             
            order(['iptv1.product_id DESC', 'iptvi2.product_id DESC', 'iptvi3.product_id DESC', 'iptvi4.product_id DESC', 'iptvi5.product_id DESC', 'iptvi6.product_id DESC'])->
            limit(1);

            $arrX = $tabForm->getData()[0];

            $arrX['storeConcatAttr'] = ($arrX['storeAttr'] === null) ? 0 : $arrX['storeAttr'];
            $arrX['storeConcatAttr2'] = ($arrX['storeAttr2'] === null) ? 0 : $arrX['storeAttr2'];
            $arrX['storeConcatAttr4'] = ($arrX['storeAttr4'] === null) ? 0 : $arrX['storeAttr4'];
            $arrX['storeConcatAttr5'] = ($arrX['prodAttr5'] === null) ? 0 : $arrX['prodAttr5'];
            $arrX['storeConcatAttr6'] = ($arrX['prodAttr6'] === null) ? 0 : $arrX['prodAttr6'];

            $arrX['prodConcatAttr'] = ($arrX['prodAttr'] === null) ? 0 : $arrX['prodAttr'];
            $arrX['prodConcatAttr2'] = ($arrX['prodAttr2'] === null) ? 0 : $arrX['prodAttr2'];
            $arrX['prodConcatAttr4'] = ($arrX['prodAttr4'] === null) ? 0 : $arrX['prodAttr4'];
            $arrX['prodConcatAttr5'] = ($arrX['prodAttr5'] === null) ? 0 : $arrX['prodAttr5'];
            $arrX['prodConcatAttr6'] = ($arrX['prodAttr6'] === null) ? 0 : $arrX['prodAttr6'];
            $arrX['group_name'] = ($arrX['groupname'] === null) ? 0 : $arrX['groupname'];

            $tabForm = $arrX;


            /*$tabForm->getSelect()->columns([
                'group_name' => 'GROUP_CONCAT(DISTINCT groupname SEPARATOR \', \')',
                'storeConcatAttr' => 'GROUP_CONCAT(DISTINCT IF(storeAttr IS NULL,0,storeAttr) SEPARATOR \', \')',
                'storeConcatAttr2' => 'GROUP_CONCAT(DISTINCT IF(storeAttr2 IS NULL,0,storeAttr2) SEPARATOR \', \')',
                'storeConcatAttr4' => 'GROUP_CONCAT(DISTINCT IF(storeAttr4 IS NULL,0,storeAttr4) SEPARATOR \', \')',
                'storeConcatAttr5' => 'GROUP_CONCAT(DISTINCT IF(prodAttr5 IS NULL,0,prodAttr5) SEPARATOR \', \')',
                'storeConcatAttr6' => 'GROUP_CONCAT(DISTINCT IF(prodAttr6 IS NULL,0,prodAttr6) SEPARATOR \', \')',
                'prodConcatAttr' => 'GROUP_CONCAT(DISTINCT IF(prodAttr IS NULL,0,prodAttr) SEPARATOR \', \')',
                'prodConcatAttr2' => 'GROUP_CONCAT(DISTINCT IF(prodAttr2 IS NULL,0,prodAttr2) SEPARATOR \', \')',
                'prodConcatAttr4' => 'GROUP_CONCAT(DISTINCT IF(prodAttr4 IS NULL,0,prodAttr4) SEPARATOR \', \')',
                'prodConcatAttr5' => 'GROUP_CONCAT(DISTINCT IF(prodAttr5 IS NULL,0,prodAttr5) SEPARATOR \', \')',
                'prodConcatAttr6' => 'GROUP_CONCAT(DISTINCT IF(prodAttr6 IS NULL,0,prodAttr6) SEPARATOR \', \')',
            ]);*/
        }
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToSelect(['label' => 'customer_group_code']);
        $collection->removeFieldFromSelect('customer_group_id');
        $collection->addFieldToSelect('customer_group_id', 'value')->getSelect();
        $data = $collection->getData();
        foreach ($data as $group) {
            $allGroup[] = $group['value'];
        }
        $arrData = [];
        $prefix = 'itoris_global_tabs_';
        $form->setHtmlIdPrefix($prefix);
        if ($tab_id != NULL) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => $this->escapeHtml(__('Product Tab Edit')), 'class' => 'fieldset-wide']
            );
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => $this->escapeHtml(__('Add New Tab')), 'class' => 'fieldset-wide']
            );
        }
        if ($tab_id != NULL && $storeId != NULL && $prod_id == NULL) {
            $dataForm = $tabForm->getData();
            $dataForm = $tabForm->getData();
            $dataForm = array_shift($dataForm);
            $dataForm['storeAttrs'] = $soreAttribute = 'label:' . $dataForm['storeAttr'] . ',is_active:' . $dataForm['storeAttr2'] . ',content:' . $dataForm['storeAttr4'] . ',show_purchased:' . $dataForm['storeAttr5'] . ',group:' . $dataForm['storeAttr6'];
            $dataForm['tab_id'] = $tab_id;
        } elseif ($tab_id != NULL && $prod_id == NULL) {
            $dataForm = $tabForm->getData();
            $dataForm = $tabForm->getData();
            $dataForm = array_shift($dataForm);
            $dataForm['tab_id'] = $tab_id;
        } elseif ($tab_id != NULL && $storeId != NULL && $prod_id != NULL) {
            $dataForm = $tabForm;
     //       $dataForm = $tabForm->getData();
     //       $dataForm = $tabForm->getData();
     //       $dataForm = array_shift($dataForm);
            $dataForm['tab_id'] = $tab_id;
            $dataForm['storeProdAttrs'] = $soreAttribute = 'label:' . $dataForm['storeAttr'] . ':' . $dataForm['prodAttr'] . ',is_active:' . $dataForm['storeAttr2'] . ':' . $dataForm['prodAttr2'] . ',content:' . $dataForm['storeAttr4'] . ':' . $dataForm['prodAttr4'] . ',show_purchased:' . $dataForm['storeAttr5'] . ':' . $dataForm['prodAttr5'] . ',group:' . $dataForm['storeAttr6'] . ':' . $dataForm['prodAttr6'];
            $dataForm['prod_id'] = $prod_id;
        } elseif ($tab_id != NULL && $prod_id != NULL && $storeId == NULL) {
            $dataForm = $tabForm;
            //        $dataForm = $tabForm->getData();
            //       $dataForm = array_shift($dataForm);
            //       $dataForm = $arrX;

            $dataForm['prodAttrs'] = $soreAttribute = 'label:' . $dataForm['prodAttr'] . ',is_active:' . $dataForm['prodAttr2'] . ',content:' . $dataForm['prodAttr4'] . ',show_purchased:' . $dataForm['prodAttr5'] . ',group:' . $dataForm['prodAttr6'];
            $dataForm['tab_id'] = $tab_id;
            $dataForm['prod_id'] = $prod_id;
        }
        $config = $this->objectManager->create('Magento\Cms\Model\Wysiwyg\Config');
        $allGroup[count($allGroup)] = -1;

        $allGroup = implode(',', $allGroup);

        if ($tab_id == null)
            $formAllgroup = [['label' => 'All Groups', 'value' => $allGroup]];
        else {
            $gr = explode(',', $dataForm['group']);
            if (in_array(-1, $gr))
                $formAllgroup = [['label' => 'All Groups', 'value' => $dataForm['group']]];
            else
                $formAllgroup = [['label' => 'All Groups', 'value' => $allGroup]];
        }

        $data = array_merge($formAllgroup, $data);
        if ($prod_id == NULL) {
            if ((isset($dataForm['storeAttr']) && $this->getRequest()->getParam('store') == $dataForm['storeAttr'])) {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry',
                        'can_use_default_value' => true,

                    ])->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");

            } elseif ($this->getRequest()->getParam('store') != null) {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'disabled' => 1,
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry disabled',
                        'can_use_default_value' => true,

                    ])->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry',
                        'can_use_default_value' => true,

                    ]);
            }
            if ((isset($dataForm['storeAttr2']) && $this->getRequest()->getParam('store') == $dataForm['storeAttr2'])) {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [0 => $this->escapeHtml(__('Disabled')), 1 => $this->escapeHtml(__('Enabled'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
                /*if (!$model->getId()) {
                    $model->setData('is_active', '1');
                }*/
            } elseif ($this->getRequest()->getParam('store') != null) {

                $fieldset->addField(
                    'is_active',
                    'select',
                    [

                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'disabled' => 1,
                        'options' => [1 => $this->escapeHtml(__('Enabled')), 0 => $this->escapeHtml(__('Disabled'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['is_active']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [0 => $this->escapeHtml(__('Disabled')), 1 => $this->escapeHtml(__('Enabled'))]
                    ]
                );
            }
            if ((isset($dataForm['storeAttr4']) && $this->getRequest()->getParam('store') == $dataForm['storeAttr4'])) {

                $fieldset->addField(
                    'content',
                    'editor',

                    [

                        'name' => 'content',
                        'class' => 'validate-text',
                        'label' => $this->escapeHtml(__('Content')),
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'config' => $config->getConfig()

                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default[content]\" class=\"use-default-control\" id=\"name_default\"  onclick=\"enableDisableWysiwyg(this)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
        <script type='text/javascript'>  function enableDisableWysiwyg(thisPraram){
        var el=thisPraram.parentNode.parentNode.firstChild;
                toggleValueElements(thisPraram, thisPraram.parentNode.parentNode.parentNode)
                if(el.classList.contains('product_tabs_wisywig_disabled')){
                    el.classList.remove('product_tabs_wisywig_disabled');
                }else {
                 el.classList.add('product_tabs_wisywig_disabled');
                }
            }</script>
         ");
            } elseif ($this->getRequest()->getParam('store') != null) {
                $fieldset->addField(
                    'content',
                    'editor',

                    [
                        'render' => 'Itoris\Producttabsslider\Block\Adminhtml\Editor',
                        'name' => 'content',
                        'class' => 'validate-text required-entry',
                        'label' => $this->escapeHtml(__('Content')),
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'disabled' => 1,
                        'editor_disabled' => true,
                        'config' => $config->getConfig(),

                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default[content]\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\"   onclick=\"enableDisableWysiwyg(this)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
        <script type='text/javascript'>  function enableDisableWysiwyg(thisPraram){
        var el=thisPraram.parentNode.parentNode.firstChild;
                toggleValueElements(thisPraram, thisPraram.parentNode.parentNode.parentNode)
                if(el.classList.contains('product_tabs_wisywig_disabled')){
                    el.classList.remove('product_tabs_wisywig_disabled');
                }else {
                 el.classList.add('product_tabs_wisywig_disabled');
                }
            }</script>
         ");
            } else {
                $fieldset->addField(
                    'content',
                    'editor',

                    [

                        'name' => 'content',
                        'class' => 'validate-text required-entry',
                        'label' => $this->escapeHtml(__('Content')),
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'config' => $config->getConfig()

                    ]
                );
            }
            /*if ((isset($dataForm['storeAttr5']) && $this->getRequest()->getParam('store') == $dataForm['storeAttr5'])) {
                $fieldset->addField(
                    'show_purchased',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Show if Product Purchased')),
                        'title' => $this->escapeHtml(__('Show if Product Purchased')),
                        'name' => 'show_purchased',
                        'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['show_purchased']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } elseif($this->getRequest()->getParam('store')!=NULL) {
                $fieldset->addField(
                    'show_purchased',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Show if Product Purchased')),
                        'title' => $this->escapeHtml(__('Show if Product Purchased')),
                        'disabled' => 1,
                        'name' => 'show_purchased',
                        'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['show_purchased']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            }else{
                $fieldset->addField(
                    'show_purchased',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Show if Product Purchased')),
                        'title' => $this->escapeHtml(__('Show if Product Purchased')),
                        'name' => 'show_purchased',
                        'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                    ]
                );
            }*/

            if (isset($dataForm['storeAttr6']) && $this->getRequest()->getParam('store') == $dataForm['storeAttr6']) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['group']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } elseif ($this->getRequest()->getParam('store') != Null) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,
                        'disabled' => 1,


                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['group']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                );
            }
        } else {
 //           echo 'Privet!';die;
            if (isset($dataForm) && $prod_id && $this->getRequest()->getParam('store') == NULL) {
                $concatAttr = $dataForm['prodConcatAttr'] . ',' . $dataForm['prodConcatAttr2'] . ',' . $dataForm['prodConcatAttr4'] . ',' . $dataForm['prodConcatAttr5'] . ',' . $dataForm['prodConcatAttr6'];
                $concatAttr = explode(',', $concatAttr);
            }
            if (isset($dataForm) && $prod_id && $this->getRequest()->getParam('store') != NULL) {
                $storeConcatAttr = $dataForm['storeConcatAttr'] . ',' . $dataForm['storeConcatAttr2'] . ',' . $dataForm['storeConcatAttr4'] . ',' . $dataForm['storeConcatAttr5'] . ',' . $dataForm['storeConcatAttr6'];
                $storeConcatAttr = explode(',', $storeConcatAttr);
            }

            if ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr']) && isset($dataForm['prodAttr']) && $dataForm['prodAttr'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr']) && !in_array(0, $storeConcatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry',
                        'can_use_default_value' => true,

                    ]);

            } elseif ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr']) && isset($dataForm['prodAttr']) && $dataForm['prodAttr'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr']) && in_array(0, $storeConcatAttr)) {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry disabled',
                        'can_use_default_value' => true,

                    ])->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr']) && $dataForm['prodAttr'] == $prod_id && !in_array(0, $concatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry',
                        'can_use_default_value' => true,

                    ]);

            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr']) && $dataForm['prodAttr'] == $prod_id && in_array(0, $concatAttr)) {

   //             \Zend_Debug::dump(    $concatAttr     );die;

                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry disabled',
                        'can_use_default_value' => true,

                    ])->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {
                $fieldset->addField(
                    'label',
                    'text',
                    [
                        'disabled' => 1,
                        'name' => 'label', 'label' => $this->escapeHtml(__('Label')), 'title' => $this->escapeHtml(__('Label')), 'required' => true,
                        'class' => 'validate-text required-entry disabled',
                        'can_use_default_value' => true,

                    ])->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['label']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            }
            if ((isset($dataForm['storeAttr2']) && isset($dataForm['prodAttr2']) && $dataForm['prodAttr2'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr2']) && !in_array(0, $storeConcatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [0 => $this->escapeHtml(__('Disabled')), 1 => $this->escapeHtml(__('Enabled'))]
                    ]
                );
                /*if (!$model->getId()) {
                    $model->setData('is_active', '1');
                }*/
            } elseif ((isset($dataForm['storeAttr2']) && isset($dataForm['prodAttr2']) && $dataForm['prodAttr2'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr2']) && in_array(0, $storeConcatAttr)) {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [

                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [1 => $this->escapeHtml(__('Enabled')), 0 => $this->escapeHtml(__('Disabled'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['is_active']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr2']) && $dataForm['prodAttr2'] == $prod_id && !in_array(0, $concatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [
                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [0 => $this->escapeHtml(__('Disabled')), 1 => $this->escapeHtml(__('Enabled'))]
                    ]
                );

            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr2']) && $dataForm['prodAttr2'] == $prod_id && in_array(0, $concatAttr)) {
                $fieldset->addField(
                    'is_active',
                    'select',
                    [

                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'options' => [1 => $this->escapeHtml(__('Enabled')), 0 => $this->escapeHtml(__('Disabled'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['is_active']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {

                $fieldset->addField(
                    'is_active',
                    'select',
                    [

                        'label' => $this->escapeHtml(__('Status')),
                        'title' => $this->escapeHtml(__('Status')),
                        'name' => 'is_active',
                        'disabled' => 1,
                        'options' => [1 => $this->escapeHtml(__('Enabled')), 0 => $this->escapeHtml(__('Disabled'))]
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['is_active']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            }
            if ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr4']) && isset($dataForm['prodAttr4']) && $dataForm['prodAttr4'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr4']) && !in_array(0, $storeConcatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'class' => 'validate-text required-entry',
                        'name' => 'content',
                        'label' => $this->escapeHtml(__('Content')),
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'is_html_allowed_on_front' => true,
                        'config' => $config->getConfig(),
                        'required' => true
                    ]
                )->setAfterElementHtml("<script> ;</script>");
            } elseif ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr4']) && isset($dataForm['prodAttr4']) && $dataForm['prodAttr4'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr4']) && in_array(0, $storeConcatAttr)) {
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name' => 'content',
                        'label' => $this->escapeHtml(__('Content')),
                        'class' => 'validate-text required-entry',
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'config' => $config->getConfig(),

                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['content']\" class=\"use-default-control\" id=\"name_default\"    onclick=\"enableDisableWysiwyg(this)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         <script type='text/javascript'>  function enableDisableWysiwyg(thisPraram){
          toggleValueElements(thisPraram, thisPraram.parentNode.parentNode.parentNode);
        var el=thisPraram.parentNode.parentNode.firstChild;
                if(el.classList.contains('product_tabs_wisywig_disabled')){
                    el.classList.remove('product_tabs_wisywig_disabled');
                }else {
                 el.classList.add('product_tabs_wisywig_disabled');
                }
            }</script>
         ");
            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr4']) && $dataForm['prodAttr4'] == $prod_id && !in_array(0, $concatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name' => 'content',
                        'class' => 'validate-text required-entry ',
                        'label' => $this->escapeHtml(__('Content')),
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'config' => $config->getConfig(),
                        'wysiwyg' => true,
                    ]
                );

            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr4']) && $dataForm['prodAttr4'] == $prod_id && in_array(0, $concatAttr)) {
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name' => 'content',
                        'label' => $this->escapeHtml(__('Content')),
                        'class' => 'validate-text required-entry',
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'wysiwyg' => true,
                        'config' => $config->getConfig(),
                        'required' => true
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['content']\" class=\"use-default-control\" id=\"name_default\"    onclick=\"enableDisableWysiwyg(this)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         <script type='text/javascript'>  function enableDisableWysiwyg(thisPraram){
          toggleValueElements(thisPraram, thisPraram.parentNode.parentNode.parentNode);
        var el=thisPraram.parentNode.parentNode.firstChild;
                if(el.classList.contains('product_tabs_wisywig_disabled')){
                    el.classList.remove('product_tabs_wisywig_disabled');
                }else {
                 el.classList.add('product_tabs_wisywig_disabled');
                }
            }</script>
         ");
            } else {
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name' => 'content',
                        'label' => $this->escapeHtml(__('Content')),
                        'class' => 'validate-text required-entry',
                        'title' => $this->escapeHtml(__('Content')),
                        'style' => 'height:36em',
                        'disabled' => 1,
                        'editor_disabled' => true,
                        'wysiwyg' => true,
                        'config' => $config->getConfig(),
                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['content']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\"   onclick=\"enableDisableWysiwyg(this)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         <script type='text/javascript'>  function enableDisableWysiwyg(thisPraram){
        var el=thisPraram.parentNode.parentNode.firstChild;
                toggleValueElements(thisPraram, thisPraram.parentNode.parentNode.parentNode);
                if(el.classList.contains('product_tabs_wisywig_disabled')){
                    el.classList.remove('product_tabs_wisywig_disabled');
                }else {
                 el.classList.add('product_tabs_wisywig_disabled');
                }
            }</script>
         ");
            }
            /* if ($this->getRequest()->getParam('store')!=NULL && isset($dataForm['storeAttr5']) && isset($dataForm['prodAttr5']) && $dataForm['prodAttr5']==$prod_id  && $this->getRequest()->getParam('store') == $dataForm['storeAttr5'] && !in_array(0,$storeConcatAttr) || $tab_id==null) {
                 $fieldset->addField(
                     'show_purchased',
                     'select',
                     [
                         'label' => $this->escapeHtml(__('Show if Product Purchased')),
                         'title' => $this->escapeHtml(__('Show if Product Purchased')),
                         'name' => 'show_purchased',
                         'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                     ]
                 );
             }elseif($this->getRequest()->getParam('store')!=NULL && isset($dataForm['storeAttr5']) && isset($dataForm['prodAttr5']) && $dataForm['prodAttr5']==$prod_id  && $this->getRequest()->getParam('store') == $dataForm['storeAttr5'] && in_array(0,$storeConcatAttr)){
                 $fieldset->addField(
                     'show_purchased',
                     'select',
                     [
                         'label' => $this->escapeHtml(__('Show if Product Purchased')),
                         'title' => $this->escapeHtml(__('Show if Product Purchased')),
                         'name' => 'show_purchased',
                         'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                     ]
                 )->setAfterElementHtml("
          <label for=\"name_default\" class=\"choice use-default\">
             <input type=\"checkbox\" name=\"use_default['show_purchased']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
             <span class=\"use-default-label\">Use Default Value</span>
         </label>
          ");
             } elseif($this->getRequest()->getParam('store')==NULL && isset($dataForm['prodAttr5']) && $dataForm['prodAttr5']==$prod_id && !in_array(0,$concatAttr) || $tab_id==null){
                 $fieldset->addField(
                     'show_purchased',
                     'select',
                     [
                         'label' => $this->escapeHtml(__('Show if Product Purchased')),
                         'title' => $this->escapeHtml(__('Show if Product Purchased')),
                         'name' => 'show_purchased',
                         'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                     ]
                 );

             }elseif($this->getRequest()->getParam('store')==NULL && isset($dataForm['prodAttr5']) && $dataForm['prodAttr5']==$prod_id && in_array(0,$concatAttr) || $tab_id==null){
                 $fieldset->addField(
                     'show_purchased',
                     'select',
                     [
                         'label' => $this->escapeHtml(__('Show if Product Purchased')),
                         'title' => $this->escapeHtml(__('Show if Product Purchased')),
                         'name' => 'show_purchased',
                         'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                     ]
                 )->setAfterElementHtml("
          <label for=\"name_default\" class=\"choice use-default\">
             <input type=\"checkbox\" name=\"use_default['show_purchased']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
             <span class=\"use-default-label\">Use Default Value</span>
         </label>
          ");
             } else {
                 $fieldset->addField(
                     'show_purchased',
                     'select',
                     [
                         'label' => $this->escapeHtml(__('Show if Product Purchased')),
                         'title' => $this->escapeHtml(__('Show if Product Purchased')),
                         'disabled' => 1,
                         'name' => 'show_purchased',
                         'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
                     ]
                 )->setAfterElementHtml("
          <label for=\"name_default\" class=\"choice use-default\">
             <input type=\"checkbox\" name=\"use_default['show_purchased']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
             <span class=\"use-default-label\">Use Default Value</span>
         </label>
          ");
             }*/

            if ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr6']) && isset($dataForm['prodAttr6']) && $dataForm['prodAttr6'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr6']) && !in_array(0, $storeConcatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                );
            } elseif ($this->getRequest()->getParam('store') != NULL && (isset($dataForm['storeAttr6']) && isset($dataForm['prodAttr6']) && $dataForm['prodAttr6'] == $prod_id && $this->getRequest()->getParam('store') == $dataForm['storeAttr6']) && in_array(0, $storeConcatAttr)) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['group']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr6']) && $dataForm['prodAttr6'] == $prod_id && !in_array(0, $concatAttr) || $tab_id == null) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                );

            } elseif ($this->getRequest()->getParam('store') == NULL && isset($dataForm['prodAttr6']) && $dataForm['prodAttr6'] == $prod_id && in_array(0, $concatAttr)) {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,


                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['group']\" class=\"use-default-control\" id=\"name_default\"  onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            } else {
                $fieldset->addField(
                    'group',
                    'multiselect',
                    [
                        'name' => 'group[]',
                        'label' => $this->escapeHtml(__('Customer Groups')),
                        'title' => $this->escapeHtml(__('Customer Groups')),
                        'values' => $data,
                        'disabled' => 1,


                    ]
                )->setAfterElementHtml("
         <label for=\"name_default\" class=\"choice use-default\">
            <input type=\"checkbox\" name=\"use_default['group']\" class=\"use-default-control\" id=\"name_default\" checked=\"checked\" onclick=\"toggleValueElements(this, this.parentNode.parentNode.parentNode)\" value=\"name\">
            <span class=\"use-default-label\">Use Default Value</span>
        </label>
         ");
            };

        }
        if ($tab_id != NULL) {
            $fieldset->addField(
                'tab_id',
                'hidden',
                [
                    'name' => 'tab_id',

                ]
            );
        }

        if (!empty($soreAttribute)) {
            $fieldset->addField(
                'storeAttrs',
                'hidden',
                [
                    'name' => 'storeAttrs',

                ]
            );
        }
        if ($storeId != null && $tab_id != null) {
            $dataForm['store_id'] = $storeId;
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name' => 'store_id',

                ]
            );
        }
        if ($prod_id != NULL && $tab_id != null) {
            $dataForm['prod_id'] = $prod_id;
            $fieldset->addField(
                'prod_id',
                'hidden',
                [
                    'name' => 'prod_id',

                ]
            );
        }
        if ($prod_id != NULL && $storeId == NULL) {
            $fieldset->addField(
                'prodAttrs',
                'hidden',
                [
                    'name' => 'prodAttrs',

                ]
            );
        }
        if ($prod_id != NULL && $storeId != NULL) {
            $fieldset->addField(
                'storeProdAttrs',
                'hidden',
                [
                    'name' => 'storeProdAttrs',

                ]
            );
        }
        $dataForm['show_purchased'] = 1;
        $form->setUseContainer(true);
        if ($tab_id == NULL) {
            $fieldset->addField(
                'show_purchased',
                'hidden',
                [
                    'name' => 'show_purchased',
                    'value' => $dataForm['show_purchased']
                ]
            );
        } else {
            $fieldset->addField(
                'show_purchased',
                'hidden',
                [
                    'label' => $this->escapeHtml(__('Show if Product Purchased')),
                    'title' => $this->escapeHtml(__('Show if Product Purchased')),
                    'name' => 'show_purchased',
                ]
            );
        }
        if ($tab_id != NULL) {


            $dataForm['all_group'] = $allGroup;
            $fieldset->addField(
                'all_group',
                'hidden',
                [
                    'name' => 'all_group',

                ]
            );
            if (isset($dataForm['group']))
                $dataForm['group'] = explode(',', $dataForm['group']);
            if (in_array(-1, $dataForm['group'])) {
                $dataForm['group'] = [implode(',', $dataForm['group'])];
            }
            $form->setValues($dataForm);
        } else {
            $fieldset->addField(
                'all_group',
                'hidden',
                [
                    'name' => 'all_group',
                    'value' => $allGroup,

                ]
            );

            /** @var \Magento\Framework\App\ResourceConnection $resource */
            $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
            $conn = $resource->getConnection();
            $sql = "SELECT MAX(value) as orderMax FROM  {$resource->getTableName('itoris_product_tabs_value_int')} WHERE  attribute_id=3 HAVING 1";
            $orderMax = [];
            $orderMax = $conn->fetchAll($sql);
            if (count($orderMax) > 0) {
                $orderMax = array_shift($orderMax);
                $fieldset->addField(
                    'orderMax',
                    'hidden',
                    [
                        'name' => 'orderMax',
                        'value' => $orderMax['orderMax'] + 1,
                    ]
                );
            }
            if ($storeId != null) {
                $fieldset->addField(
                    'store_id',
                    'hidden',
                    [
                        'name' => 'store_id',
                        'value' => $storeId,

                    ]
                );
            }

            if ($prod_id != NULL) {
                $fieldset->addField(
                    'prod_id',
                    'hidden',
                    [
                        'name' => 'prod_id',
                        'value' => $prod_id,

                    ]
                );
            }
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

}