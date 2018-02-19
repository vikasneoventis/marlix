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
class ProductTabGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';
    protected $_template = 'Itoris_Producttabsslider::widget/grid/extended.phtml';
    protected $objectManager;
    protected function _construct(){
        parent::_construct();
        $this->setId('itoris_grid_tabs');
        $this->setUseAjax(true);
        $this->setDefaultSort('tab_id');
        $this->setPagerVisibility(false);
        $this->setSaveParametersInSession(true);
        $this->set();
        $this->setDefaultLimit(0);
        $this->objectManager=\Magento\Framework\App\ObjectManager::getInstance();

    }

    protected function cleanCashe(){
        $cacheFrontendPool = $this->objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
        foreach($cacheFrontendPool as $cacheFrontend){
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
        }

    }

    protected function _prepareCollection()
    {
        $storeId = $this->getRequest()->getParam('store');
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'label' => $this->escapeHtml(__('Reset Filter')),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary'
                ]
            )->setDataAttribute(
                [
                    'action' => 'grid-filter-reset'
                ]
            )
        );

        $idProduct=$this->getRequest()->getParam('id');
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');



        if($storeId) {

            $dataProduct= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
            $dataProduct->getSelect()->reset();
            $dataProduct->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT `main_table`.*,`iptvi6`.`product_id` as prod,`iptvi6`.`store_id` as store, `iptv1`.`value` AS `label`, `iptvi2`.`value` AS `status`, `iptvi4`.`value` AS `content`, `iptvi3`.`value` AS `order`, `iptvi5`.`value` AS `show_purchased`, `iptvi6`.`value` AS `group`,cg.customer_group_code AS `groupname` FROM {$resource->getTableName('itoris_producttabs_tabs')} AS `main_table`
                 INNER JOIN  {$resource->getTableName('itoris_product_tabs_value_varchar')} AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                 LEFT JOIN `{$resource->getTableName('customer_group')}` AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                 WHERE ((iptv1.product_id IS NULL OR iptv1.product_id={$idProduct}) AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id={$idProduct}) AND  (iptvi3.product_id IS NULL  OR iptvi3.product_id={$idProduct}) AND (iptvi5.product_id IS NULL OR iptvi5.product_id={$idProduct}) AND (iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND (iptvi4.product_id IS NULL OR iptvi4.product_id={$idProduct}) AND  (iptvi6.product_id IS NULL OR iptvi6.product_id={$idProduct})
                 AND ((iptv1.store_id  IS NULL OR iptv1.store_id={$storeId}) AND (iptvi2.store_id IS NULL OR iptvi2.store_id={$storeId}) AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$storeId}) AND (iptvi5.store_id IS NULL OR iptvi5.store_id={$storeId})  AND (iptvi3.store_id IS NULL OR iptvi3.store_id={$storeId})  AND (iptvi4.store_id IS NULL OR iptvi4.store_id={$storeId}) AND (iptvi6.store_id IS NULL OR iptvi6.store_id={$storeId}))) Having 1  
                 ORDER BY iptv1.store_id DESC, iptv1.value_id DESC,iptvi2.store_id DESC, iptvi2.value_id DESC,iptvi3.store_id DESC, iptvi3.value_id DESC,iptvi4.store_id DESC,iptvi4.value_id DESC,iptvi5.store_id DESC,iptvi5.value_id DESC,iptvi6.store_id DESC,iptvi6.value_id DESC )
          "));

                 $dataProduct->getSelect()
                ->group('t.tab_id')->order('order');
            $dataProduct->getSelect()->columns([
                    'group_name' => 'GROUP_CONCAT(DISTINCT groupname SEPARATOR \', \')',
                    'group_prod_name' => 'GROUP_CONCAT(DISTINCT IF(prod IS NULL,\'\',groupname) SEPARATOR \',\')',
                    'group_store_name'=>'GROUP_CONCAT(DISTINCT IF(store IS NULL,\'\',groupname) SEPARATOR \',\')'
                ]);

        }else{
//  CONCAT(IF(`iptv1`.store_id IS NOT NULL,`iptv1`.store_id,0),',',IF(`iptvi2`.store_id IS NOT NULL,`iptvi2`.store_id,0),',',IF(`iptvi3`.store_id IS NOT NULL,`iptvi3`.store_id,0),',',IF(`iptvi4`.store_id IS NOT NULL,`iptvi4`.store_id,0),',',IF(`iptvi5`.store_id IS NOT NULL,`iptvi5`.store_id,0),',',IF(`iptvi6`.store_id IS NOT NULL,`iptvi6`.store_id,0)) as storeconcat,

            $dataProduct= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
            $dataProduct->getSelect()->reset();
            $dataProduct->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT `main_table`.*, `iptv1`.`value` AS `label`,`iptvi6`.`product_id` as prod, `iptvi2`.`value` AS `status`, `iptvi4`.`value` AS `content`, `iptvi3`.`value` AS `order`, `iptvi5`.`value` AS `show_purchased`, `iptvi6`.`value` AS `group`,cg.customer_group_code AS `groupname` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_varchar')} AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                 INNER JOIN `{$resource->getTableName('customer_group')}` AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                 WHERE ((iptv1.product_id IS NULL OR iptv1.product_id={$idProduct}) AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id={$idProduct}) AND (iptvi3.product_id IS NULL  OR iptvi3.product_id={$idProduct}) AND (iptvi5.product_id IS NULL OR iptvi5.product_id={$idProduct}) AND (iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND (iptvi4.product_id IS NULL OR iptvi4.product_id={$idProduct}) AND  (iptvi6.product_id IS NULL OR iptvi6.product_id={$idProduct})
                 AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) Having 1 ORDER BY iptv1.value_id DESC ,iptvi2.value_id DESC,iptvi3.value_id DESC,iptvi4.value_id DESC,iptvi5.value_id DESC,iptvi3.value_id DESC,iptvi6.value_id DESC)
          "))->group('t.tab_id')->order('order');
            $dataProduct->getSelect()->columns([
                    'group_name' => 'GROUP_CONCAT(DISTINCT groupname SEPARATOR \',\')',
                    'group_prod_name' => 'GROUP_CONCAT(DISTINCT IF(prod IS NULL,\'\',groupname) SEPARATOR \',\')'
                ]);
        }
        $this->setCollection($dataProduct);
        $this->setFilterVisibility(false);
        return parent::_prepareCollection();
    }

    private function isTabStore($tabId)
    {
        $store=$this->getRequest()->getParam('store');
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();
        $query = "SELECT * FROM {$resource->getTableName('itoris_product_tabs_value_int')}
             WHERE tab_id={$tabId} AND  store_id={$store}  AND  attribute_id=3;";
        $sortStore = $conn->fetchAll($query);

        return $sortStore;
    }

    private function getOrderKey($store)
    {
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();

        if($store){
            $query = "SELECT value FROM {$resource->getTableName('itoris_product_tabs_value_int')}
             WHERE store_id={$store}  AND  attribute_id=3;";
        }else{
            $query = "SELECT value FROM {$resource->getTableName('itoris_product_tabs_value_int')}
             WHERE store_id is NULL  AND  attribute_id=3;";
        }
        $sortStore = $conn->fetchAll($query);
        return $sortStore;
    }

    /**
     * Prepare grid columns
     */
    protected function _prepareColumns(){

        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        $globalTabsCount= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        $globalTabsCount->getSelect()->reset();
        $idProduct=$this->getRequest()->getParam('id');
        if($this->getRequest()->getParam('store')==null) {
            $globalTabsCount->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT * FROM (SELECT iptvi3.value as gr,iptvi3.value_id as val,`main_table`.tab_id as mtid,iptvi3.product_id as store  FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
             WHERE ((iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct})  AND iptvi3.store_id IS NULL) HAVING 1  ORDER BY iptvi3.product_id DESC ) as tt GROUP BY mtid ORDER BY gr ASC)
          "));
        }else{

            $store=$this->getRequest()->getParam('store');
            $globalTabsCount->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT * FROM (SELECT iptvi3.value as gr,iptvi3.value_id as val,`main_table`.tab_id as mtid,iptvi3.product_id as store  FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
             WHERE ((iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND  ( iptvi3.store_id IS NULL OR iptvi3.store_id={$store})) HAVING 1   ORDER BY iptvi3.product_id DESC,iptvi3.store_id DESC ) as tt GROUP BY mtid ORDER BY gr ASC)
          "));
        }

        $store=$this->getRequest()->getParam('store');
        $globalTabsConcat = [];
        $idValueConcat = [];
        $idConcat = [];

        foreach ($globalTabsCount->getData() as $item ){
            if( in_array( $item['gr'], $globalTabsConcat)){
                $check = false;
                if( $this->isTabStore($item['mtid']) ){
                    $idFix = array_search( $item['gr'], $globalTabsConcat);
                    $check = true;
                }else{
                    $idFix =  $item['mtid'];
                }
                    $storeOrderNull_old = $this->getOrderKey(0);
                    $storeOrder_old = $this->getOrderKey($store);
                $storeOrderNull = [];
                    foreach ($storeOrderNull_old as $value){
                        $storeOrderNull[] = $value['value'];
                    }
                $storeOrder = [];
                foreach ($storeOrder_old as $value){
                    $storeOrder[] = $value['value'];
                }
                    $buff = 0;
                    foreach ($storeOrderNull as $value){
                        if(!in_array($value, $storeOrder)){
                            $buff = $value;
                            break;
                        }
                    }

                    $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
                    $conn = $resource->getConnection();
                    $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('store')},{$idFix},{$idProduct},{$buff},3)";

                    $conn->query($sql);
                    $this->cleanCashe();

                    if($check){
                         $globalTabsConcat[$idFix] = $buff;
                        $globalTabsConcat[$item['mtid']] = $item['gr'];
                    }else{
                        $globalTabsConcat[$item['mtid']] = $buff;
                    }

            }else{
                $globalTabsConcat[$item['mtid']] = $item['gr'];
            }

            $idValueConcat[] = $item['val'];

            if( !in_array( $item['mtid'], $idConcat)){
                $idConcat[] = $item['mtid'];
            }
        }


        asort($globalTabsConcat);
        $globalTabsConcat = array_values($globalTabsConcat);

        $globalTabs= $this->objectManager->create('Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\Collection');
        $globalTabs->getSelect()->reset();
        if($this->getRequest()->getParam('store')==null) {
            $globalTabs->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT main_table.tab_id ,`iptvi3`.`value` AS `order` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table` INNER JOIN
            {$resource->getTableName('itoris_product_tabs_value_varchar')} AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
            INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
            INNER JOIN `{$resource->getTableName('customer_group')}` AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
             WHERE (iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND iptvi6.product_id IS NULL AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) ORDER BY main_table.tab_id ASC )
          "))->group('t.tab_id')->order('order');
        }else{
            $globalTabs->getSelect()->from(
                new \Zend_Db_Expr
                ("(SELECT `main_table`.*, `iptv1`.`value` AS `label`, `iptvi2`.`value` AS `status`, `iptvi4`.`value` AS `content`, `iptvi3`.`value` AS `order`, `iptvi5`.`value` AS `show_purchased`, `iptvi6`.`value` AS `group`,cg.customer_group_code AS `groupname` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_varchar')} AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_int')} AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                 INNER JOIN {$resource->getTableName('itoris_product_tabs_value_text')} AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                 INNER JOIN `{$resource->getTableName('customer_group')}` AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                 WHERE ((iptv1.product_id IS NULL OR iptv1.product_id={$idProduct}) AND  (iptvi2.product_id IS NULL  OR iptvi2.product_id={$idProduct}) AND (iptvi5.product_id IS NULL OR iptvi5.product_id={$idProduct}) AND (iptvi3.product_id IS NULL OR iptvi3.product_id={$idProduct}) AND (iptvi4.product_id IS NULL OR iptvi4.product_id={$idProduct}) AND  (iptvi6.product_id IS NULL OR iptvi6.product_id={$idProduct}) AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL))
          "))->group('t.tab_id')->order('order');

        }
        $idTabs=[];
        $globalTabs=$globalTabs->getData();

        $orderArrStore = [];
        foreach($globalTabs as $gt){
            $orderArrStore[] = $gt["order"];
            $idTabs[]=$gt['tab_id'];
        }
        $storeId = $this->getRequest()->getParam('store');
        $idProduct=$this->getRequest()->getParam('id');
        $this->addColumn('label', [
            'header'    => $this->escapeHtml(__('Tab Label')),
            'index'     => 'label',
            'filter'=>false,
            'sortable'  => false
        ]);

        $this->addColumn('group_name', [
            'header'    => $this->escapeHtml(__('Customer Groups')),
            'renderer'    => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\Text',
            'index'     => 'group_name',
            'filter'=>false,
            'sortable'  => false
        ]);
        if($storeId==NULL) {

            $this->addColumn('order', [
                'header' => $this->escapeHtml(__('Order')),
                'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\ActionOrder',
                'index' => 'order',
                'order' => $globalTabsConcat,
                'id_concat' => $idConcat,
                'id_product' => $idProduct,
                'id_value'=>$idValueConcat,
                'type' => 'action',
                'actions' => [
                    [
                        'caption' => $this->getBaseUrl(),
                        'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                        'field' => 'id'

                    ]
                ],
                'filter' => false,
                'sortable' => false
            ]);
        }else{

            $this->addColumn('order', [
                'header' => $this->escapeHtml(__('Order')),
                'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\Render\ActionOrder',
                'index' => 'order',
                'order' =>  $globalTabsConcat,
                'id_concat' => $idConcat,
                'id_product' => $idProduct,
                'id_store'=>$storeId,
                'id_value'=>$idValueConcat,
                'type' => 'action',
                'actions' => [
                    [
                        'caption' => $this->getBaseUrl(),
                        'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                        'field' => 'id'
                    ]
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
        if($storeId==null) {
            $this->addColumn('action',
                [
                    'id_global'=>$idTabs,
                    'header' => $this->escapeHtml(__('Action')),
                    'width' => '100',
                    'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\CustomAction',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => $this->escapeHtml(__('Edit')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                            'field' => "prod_id/{$idProduct}/id"

                        ],
                        [
                            'caption' => $this->escapeHtml(__('Delete')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/delete'],
                            'field' => 'tab_id'

                        ]
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                ]);

        }else{
            $this->addColumn('action',
                [
                    'id_global'=>$idTabs,
                    'header' => $this->escapeHtml(__('Action')),
                    'width' => '100',
                    'renderer' => 'Itoris\Producttabsslider\Block\Adminhtml\Grid\CustomAction',

                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => $this->escapeHtml(__('Edit')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/edit'],
                            'field' => "store/{$storeId}/prod_id/{$idProduct}/id"
                        ],
                        [
                            'caption' => $this->escapeHtml(__('Delete')),
                            'url' => ['base' => 'itorisproducttabs/producttabs/delete'],
                            'field' => 'tab_id'
                        ]
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                ]);
        }

        return parent::_prepareColumns();
    }
    public function newTabsSave(){
        return $this->getUrl('itorisproducttabs/producttabs/save',['form_key'=>$this->getFormKey()]);
    }
    public function getGridUrl()
    {
        return $this->getUrl('itorisproducttabs/producttabs/product', ['_current'=>true]);
    }
    public function addNewUrl(){
        if(!$this->getRequest()->getParam('store'))
            return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey(),'prod_id'=>$this->getRequest()->getParam('id')]);
        return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey(),'prod_id'=>$this->getRequest()->getParam('id'),'store'=>$this->getRequest()->getParam('store')]);

    }
    public function buildUrl(){
        $id_prod=$this->getRequest()->getParam('id');
        $store=$this->getRequest()->getParam('store');
        if($id_prod && !$store)
            return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey(),'prod_id'=>$id_prod]);
        if($id_prod && $store)
            return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey(),'prod_id'=>$id_prod,'store'=>$store]);
        if(!$id_prod && $store)
            return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey(),'store'=>$store]);
        return $this->getUrl('itorisproducttabs/producttabs/ajax',['form_key'=>$this->getFormKey()]);

    }
    public function deleteAjax($id_prod=false,$store=false){
        return $this->getUrl('itorisproducttabs/producttabs/deleteAjax',['form_key'=>$this->getFormKey()]);


    }
    public function getSortUrl(){
        return $this->getUrl('itorisproducttabs/producttabs/grid',['form_key'=>$this->getFormKey()]);
    }


}