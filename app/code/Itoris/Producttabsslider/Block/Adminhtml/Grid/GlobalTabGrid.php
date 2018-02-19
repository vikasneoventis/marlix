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

namespace Itoris\Producttabsslider\Block\Adminhtml\Grid;
use Magento\Backend\Block\Widget\Grid\Column\Renderer;
class GlobalTabGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_massactionBlockName = 'Itoris\Producttabsslider\Block\Adminhtml\Grid\CustomMassAction';
    protected $objectManager;
    protected $_template = 'Itoris_Producttabsslider::widget/grid/extendedGlobal.phtml';
    protected function _construct(){
        parent::_construct();
        $this->setId('itoris_grid_tabs');
        $this->setUseAjax(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('tab_id');
        $this->setSaveParametersInSession(true);
        $this->objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $this->setDefaultLimit(0);
    }
    /**
     * Prepare customers collection
     *
     * @return this
     */
    protected function _prepareCollection()
    {
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        /** @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $storeManager = $this->objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $collection =  $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        if($this->getRequest()->getParam('store')==Null) {
            $collection->getSelect()
                ->join("{$resource->getTableName('itoris_product_tabs_value_varchar')} as iptv1", "main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1",
                    ['label' => 'iptv1.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi2", "main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2", ['status' => 'iptvi2.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi4", "main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4", ['content' => 'iptvi4.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi3", "main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3", ['order' => 'iptvi3.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_int')} as iptvi5", "main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5", ['show_purchased' => 'iptvi5.value'])
                ->join("{$resource->getTableName('itoris_product_tabs_value_text')} as iptvi6", "main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6", ['group' => 'iptvi6.value'])
                ->join("{$resource->getTableName('customer_group')} as cg", "find_in_set(cg.customer_group_id,iptvi6.value)", ['store_name' => 0, 'group_name' => 'GROUP_CONCAT(DISTINCT cg.customer_group_code SEPARATOR \', \')'])
                ->where('iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL
             AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND iptvi6.product_id IS NULL AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL
             AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL')->group('main_table.tab_id')->order('order ASC');

        }else{
            $collection->getSelect()->reset();
            $collection->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT `main_table`.*,`iptvi6`.`product_id` as prod,`iptvi6`.`store_id` as store, `iptv1`.`value` AS `label`, `iptvi2`.`value` AS `status`, `iptvi4`.`value` AS `content`, `iptvi3`.`value` AS `order`, `iptvi5`.`value` AS `show_purchased`, `iptvi6`.`value` AS `group`,cg.customer_group_code AS `groupname` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_varchar')}` AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                 LEFT JOIN {$resource->getTableName('customer_group')} AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                 WHERE (iptv1.product_id IS NULL AND  iptvi2.product_id IS NULL  AND  iptvi3.product_id IS NULL  AND iptvi5.product_id IS NULL AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL  AND  iptvi6.product_id IS NULL
                 AND ((iptv1.store_id  IS NULL OR iptv1.store_id={$this->getRequest()->getParam('store')}) AND (iptvi2.store_id IS NULL OR iptvi2.store_id={$this->getRequest()->getParam('store')}) AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$this->getRequest()->getParam('store')}) AND (iptvi5.store_id IS NULL OR iptvi5.store_id={$this->getRequest()->getParam('store')})  AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$this->getRequest()->getParam('store')})  AND (iptvi4.store_id IS NULL OR iptvi4.store_id={$this->getRequest()->getParam('store')}) AND (iptvi6.store_id IS NULL OR iptvi6.store_id={$this->getRequest()->getParam('store')}))) Having 1 ORDER BY iptv1.value_id DESC,iptvi2.value_id DESC,iptvi3.value_id DESC,iptvi4.value_id DESC,iptvi5.value_id DESC,iptvi6.value_id DESC )
          "))->group('t.tab_id')->order('order');
            $collection->getSelect()->columns([
                    'group_name' => 'GROUP_CONCAT(DISTINCT groupname SEPARATOR \', \')',
                    'group_store_name'=>'GROUP_CONCAT(DISTINCT IF(store IS NULL,\'\',groupname) SEPARATOR \',\')'
                ]);
            $collection->setAllias('t');
        }
        $this->setCollection($collection);
        $this->setUseAjax(true);
        return parent::_prepareCollection();
    }


    /**
     * Prepare grid columns
     */
    protected function _prepareColumns(){
        $globalTabsCount= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        $globalTabsCount->getSelect()->reset();
        if($this->getRequest()->getParam('store')==NULL) {
            $globalTabsCount->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT  iptvi3.value as gr, main_table.tab_id as mtid FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table` INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_varchar')}` AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5 INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6 INNER JOIN {$resource->getTableName('customer_group')} AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value) WHERE (iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND iptvi6.product_id IS NULL AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) Having 1 ORDER BY gr ASC )
          "));
            $globalTabsCount->getSelect()->columns([
                    'groupOrder' => 'GROUP_CONCAT(DISTINCT gr SEPARATOR \', \')',
                    'id' => 'GROUP_CONCAT(DISTINCT mtid SEPARATOR \', \')'
                ]);

            $globalTabsCount=$globalTabsCount->getData();
            $globalTabsConcat = $globalTabsCount[0]['groupOrder'];
            $idConcat=$globalTabsCount[0]['id'];
            $idConcat=explode(',',$idConcat);
            $globalTabsConcat=explode(',',$globalTabsConcat);
        }else{
            $store=$this->getRequest()->getParam('store');
            $globalTabsCount->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT * FROM (SELECT iptvi3.value as gr,iptvi3.value_id as val,`main_table`.tab_id as mtid,iptvi3.product_id as store  FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
            INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
             WHERE (iptvi3.product_id IS NULL   AND  ( iptvi3.store_id IS NULL OR iptvi3.store_id={$store})) Having 1  ORDER BY iptvi3.product_id DESC,iptvi3.store_id DESC ) as tt GROUP BY mtid Having 1 ORDER BY gr ASC)
          "));
            $globalTabsCount->getSelect()->columns([
                    'groupOrder' => 'GROUP_CONCAT(DISTINCT gr SEPARATOR \', \')',
                    'prod' => 'GROUP_CONCAT(val SEPARATOR \', \')',
                    'id' => 'GROUP_CONCAT(DISTINCT mtid SEPARATOR \', \')'
                ]);

            $globalTabsCount=$globalTabsCount->getData();
            $globalTabsConcat = $globalTabsCount[0]['groupOrder'];
            $idValueConcat = $globalTabsCount[0]['prod'];
            $idConcat=$globalTabsCount[0]['id'];
            $idValueConcat=explode(',',$idValueConcat);
            $idConcat=explode(',',$idConcat);
            $globalTabsConcat=explode(',',$globalTabsConcat);
            $globalTabs= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        }

        $this->addColumn('label', [
            'header'    => $this->escapeHtml(__('Tab Label')),
            'index'     => 'label',
            'filter'=>false,
            'sortable'  => false

        ]);
        /*$this->addColumn('show_purchased', [
            'header'    => $this->escapeHtml(__('Show if purchased')),
            'index'     => 'show_purchased',
            'filter'=>false,
            'type'=>'options',
            'sortable'  => false,
            'options' => [1 => $this->escapeHtml(__('Always')), 2 => $this->escapeHtml(__('Show if Purchased')), 3 => $this->escapeHtml(__('Show if Not Purchased'))]
        ]);*/
        $this->addColumn('group_name', [
            'header'    => $this->escapeHtml(__('Customer Groups')),
            'index'     => 'group_name',
            'renderer'    => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\GlobalTextGroup',
            'filter'=>false,
            'sortable'  => false
        ]);
        if($this->getRequest()->getParam('store')==null) {
            $this->addColumn('order', [
                'header' => $this->escapeHtml(__('Order')),
                'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\ActionOrder',
                'index' => 'order',
                'order' => $globalTabsConcat,
                'id_concat' => $idConcat,
                'type' => 'action',
                'actions' => [
                    [
                        'caption' => $this->getBaseUrl(),
                        'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                        'field' => 'id'

                    ],

                ],
                'filter' => false,
                'sortable' => false

            ]);
        }else{
            $this->addColumn('order', [
                'header' => $this->escapeHtml(__('Order')),
                'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\ActionOrder',
                'index' => 'order',
                'order' => $globalTabsConcat,
                'id_concat' => $idConcat,
                'id_store'=>$this->getRequest()->getParam('store'),
                'id_value'=>$idValueConcat,
                'type' => 'action',
                'actions' => [
                    [
                        'caption' => $this->getBaseUrl(),
                        'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                        'field' => 'id',

                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]);
        }
        $this->addColumn('status', [
            'header'    => $this->escapeHtml(__('Status')),
            'index'     => 'status',
            'type'=>'options',
            'options'=>[0=>'Disabled',1=>'Enabled'],
            'filter'=>false,
            'sortable'  => false
        ]);
        if($this->getRequest()->getParam('store')==NULL) {
            $this->addColumn('action',
                [
                    'header' => $this->escapeHtml(__('Action')),
                    'width' => '100',
                    'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\Action',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => $this->escapeHtml(__('Edit')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                            'field' => 'id'
                        ],
                        [
                            'caption' => $this->escapeHtml(__('Delete')),
                            'confirm' => $this->escapeHtml(__('Are you sure want to remove the tab(s)?')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/deleteTabs'],
                            'field' => 'id'
                        ],
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                ]);

        }else{
            $this->addColumn('action',
                [
                    'header' => $this->escapeHtml(__('Action')),
                    'width' => '100',
                    'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\Action',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => $this->escapeHtml(__('Edit')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                            'field' => "store/{$this->getRequest()->getParam('store')}/id",

                        ],
                        [
                            'caption' => $this->escapeHtml(__('Delete')),
                            'confirm' => $this->escapeHtml(__('Are you sure want to remove the tab(s)?')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/deleteTabs'],
                            'field' => 'id'

                        ],

                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                ]);
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tab_id');
        $this->getMassactionBlock()->setFormFieldName('tab_id');
        if($this->getRequest()->getParam('store')==NULL) {
            $this->getMassactionBlock()->addItem(
                'delete',
                [
                    'label' => $this->escapeHtml(__('Delete')),
                    'url' => $this->getUrl('*/*/massDelete'),
                    'confirm' => $this->escapeHtml(__('Are you sure want to remove the tab(s)?'))
                ]
            );
        }
        if($this->getRequest()->getParam('store')==NULL) {
            $this->getMassactionBlock()->addItem(
                'enable',
                [
                    'label' => $this->escapeHtml(__('Enable')),
                    'url' => $this->getUrl('*/*/massEnable'),
                    'confirm' => $this->escapeHtml(__('Are you sure want to enable the tab(s)?'))
                ]
            );
            $this->getMassactionBlock()->addItem(
                'disable',
                [
                    'label' => $this->escapeHtml(__('Disable')),
                    'url' => $this->getUrl('*/*/massDisable'),
                    'confirm' => $this->escapeHtml(__('Are you sure want to disable the tab(s)?'))
                ]
            );


        }else{
            $this->getMassactionBlock()->addItem(
                'enable',
                [
                    'label' => $this->escapeHtml(__('Enable')),
                    'url' => $this->getUrl('*/*/massEnable',['store'=>$this->getRequest()->getParam('store')]),
                    'confirm' => $this->escapeHtml(__('Are you sure want to enable the tab(s)?'))
                ]
            );
            $this->getMassactionBlock()->addItem(
                'disable',
                [
                    'label' => $this->escapeHtml(__('Disable')),
                    'url' => $this->getUrl('*/*/massDisable',['store'=>$this->getRequest()->getParam('store')]),
                    'confirm' => $this->escapeHtml(__('Are you sure want to disable the tab(s)?'))
                ]
            );
        }


        return $this;
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
    public function getSortUrl(){
        return $this->getUrl('*/*/grid',['form_key'=>$this->getFormKey()]);
    }


}