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

namespace Itoris\Producttabsslider\Controller\Adminhtml\Producttabs;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;
class Grid extends \Magento\Backend\App\Action
{
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';
    protected function cleanCashe(){
        $cacheFrontendPool = $this->_objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
        foreach($cacheFrontendPool as $cacheFrontend){
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
        }

    }

    public function execute()
    {
        $jsonFactory = $this->_objectManager->create('\Magento\Framework\Controller\Result\JsonFactory');
        if(!$this->getRequest()->getParam('id_product') && !$this->getRequest()->getParam('id_store')) {
                    if ($this->getRequest()->getParam('down') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi set iptvi.value={$this->getRequest()->getParam('this_sort')}
                        WHERE iptvi.tab_id={$this->getRequest()->getParam('id_down')} AND iptvi.attribute_id=3 AND  product_id IS NULL";
                        $conn->query($sql);
                        $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi1 set iptvi1.value={$this->getRequest()->getParam('down')}
                        WHERE iptvi1.tab_id={$this->getRequest()->getParam('id_this')} AND iptvi1.attribute_id=3  AND store_id IS NULL AND product_id IS NULL";
                        $conn->query($sql);
                        $conn->query($sql1);
                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);

                    }
                    if ($this->getRequest()->getParam('up') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi set iptvi.value={$this->getRequest()->getParam('this_sort')}
                        WHERE iptvi.tab_id={$this->getRequest()->getParam('id_up')} AND iptvi.attribute_id=3 AND store_id IS NULL AND product_id IS NULL";
                        $conn->query($sql);
                        $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi1 set iptvi1.value={$this->getRequest()->getParam('up')}
                        WHERE iptvi1.tab_id={$this->getRequest()->getParam('id_this')} AND iptvi1.attribute_id=3 AND store_id IS NULL AND product_id IS NULL";
                        $conn->query($sql);
                        $conn->query($sql1);
                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);

                    }
        }elseif(!$this->getRequest()->getParam('id_product') && $this->getRequest()->getParam('id_store')){
                    if ($this->getRequest()->getParam('down') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $query="SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('nextValue')})
                          AND iptvi.product_id IS NULL AND  iptvi.attribute_id=3 HAVING 1 ORDER BY val ASC) as tt ";
                        $sortStore = $conn->fetchAll($query);
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if(count($sortStore)==2){

                            if($sortStore[0]['store']==NULL && $sortStore[1]['store']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('down')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['store']==$this->getRequest()->getParam('id_store') && $sortStore[1]['store']==$this->getRequest()->getParam('id_store')){

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['store']!=NULL && $sortStore[1]['store']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_store')},{$sortStore[0]['val']},3)";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            }else{
                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')}";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_store')},{$sortStore[1]['val']},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }
                        }

                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);

                    }
                    if ($this->getRequest()->getParam('up') && $this->getRequest()->getParam('this_sort')) {

                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $query = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('prevValue')})
                          AND iptvi.product_id IS NULL AND  iptvi.attribute_id=3 HAVING 1 ORDER BY val ASC) as tt ";

                        $sortStore = $conn->fetchAll($query);
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if (count($sortStore) == 2) {

                            if ($sortStore[0]['store'] == NULL && $sortStore[1]['store'] == NULL) {
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('up')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['store'] == $this->getRequest()->getParam('id_store') && $sortStore[1]['store'] == $this->getRequest()->getParam('id_store')) {

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['store'] != NULL && $sortStore[1]['store'] == NULL) {
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_store')},{$sortStore[1]['val']},3)";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            } else {
                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')} ";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,store_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_store')},{$sortStore[1]['val']},3)";
                                $conn->query($sql);
                                $conn->query($sql1);

                            }
                        }

                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);
                    }
        }elseif($this->getRequest()->getParam('id_product') && !$this->getRequest()->getParam('id_store')){
                    if ($this->getRequest()->getParam('down') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $query="SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('nextValue')})
                          AND iptvi.store_id IS NULL AND  iptvi.attribute_id=3 HAVING 1 ORDER BY val ASC) as tt ";

                        $sortStore = $conn->fetchAll($query);
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if(count($sortStore)==2){
                            if($sortStore[0]['product']==NULL && $sortStore[1]['product']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('down')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['product']==$this->getRequest()->getParam('id_product') && $sortStore[1]['product']==$this->getRequest()->getParam('id_product')){

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['product']!=NULL && $sortStore[1]['product']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_product')},{$sortStore[0]['val']},3)";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            }else{
                                 $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')}";
                                 $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$sortStore[1]['val']},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }
                        }

                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);

                    }
                    if ($this->getRequest()->getParam('up') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $query = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('prevValue')})
                          AND iptvi.store_id IS NULL AND  iptvi.attribute_id=3 HAVING 1 ORDER BY val ASC) as tt ";

                        $sortStore = $conn->fetchAll($query);
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if (count($sortStore) == 2) {
                            if ($sortStore[0]['product'] == NULL && $sortStore[1]['product'] == NULL) {
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('up')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['product'] == $this->getRequest()->getParam('id_product') && $sortStore[1]['product'] == $this->getRequest()->getParam('id_product')) {

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['product'] != NULL && $sortStore[1]['product'] == NULL) {
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$sortStore[0]['val']},3)";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            } else {

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')} ";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_product')},{$sortStore[1]['val']},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }
                        }

                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);
                    }

            }elseif($this->getRequest()->getParam('id_product') && $this->getRequest()->getParam('id_store')){


                    if ($this->getRequest()->getParam('down') && $this->getRequest()->getParam('this_sort')) {
                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();
                        $queryStore = $queryStoreNull = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('nextValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id is NULL AND iptvi.product_id is NULL   ORDER BY val ASC) as tt ";
                        $sortStoreGlobal = $conn->fetchAll($queryStore);

                        $queryStore = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('nextValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id={$this->getRequest()->getParam('id_store')}   AND iptvi.product_id={$this->getRequest()->getParam('id_product')}  ORDER BY val ASC) as tt ";
                        $sortStoreS = $conn->fetchAll($queryStore);

                        $queryStoreNull = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('nextValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id is NULL  AND iptvi.product_id={$this->getRequest()->getParam('id_product')}  ORDER BY val ASC) as tt ";
                        $sortStoreN = $conn->fetchAll($queryStoreNull);

                        $result1 = array_merge($sortStoreGlobal, $sortStoreN);
                        $sortStore =  array_merge($result1, $sortStoreS);

                        if(count($sortStore)==2 && $sortStore[1]['val']<$sortStore[0]['val']){
                            $sortStore = array_reverse($sortStore);
                        }

                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if(count($sortStore)==2){

                            if($sortStore[0]['store']==NULL && $sortStore[1]['store']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('down')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['store']==$this->getRequest()->getParam('id_store') && $sortStore[1]['store']==$this->getRequest()->getParam('id_store')){

                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')} AND store_id={$sortStore[1]['store']} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}  AND store_id={$sortStore[0]['store']}";

                                $conn->query($sql);
                                $conn->query($sql1);
                            }elseif($sortStore[0]['store']!=NULL && $sortStore[1]['store']==NULL){
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_down')},{$this->getRequest()->getParam('id_product')},{$sortStore[0]['val']},3)";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')} set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            }else{
                                 $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('nextValue')}";
                                 $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$sortStore[1]['val']},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            }
                        }

                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);

                    }
                    if ($this->getRequest()->getParam('up') && $this->getRequest()->getParam('this_sort')) {

                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        $conn = $resource->getConnection();

                        $queryStore = $queryStoreNull = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('prevValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id is NULL AND iptvi.product_id is NULL   ORDER BY val ASC) as tt ";
                        $sortStoreGlobal = $conn->fetchAll($queryStore);

                        $queryStore = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('prevValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id={$this->getRequest()->getParam('id_store')}   AND iptvi.product_id={$this->getRequest()->getParam('id_product')}  ORDER BY val ASC) as tt ";
                        $sortStoreS = $conn->fetchAll($queryStore);

                        $queryStoreNull = "SELECT * FROM (SELECT iptvi.tab_id as tab,iptvi.product_id as product,iptvi.store_id as store,iptvi.value as val  FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi WHERE (value_id={$this->getRequest()->getParam('thisValue')} OR value_id={$this->getRequest()->getParam('prevValue')})
                                  AND   iptvi.attribute_id=3 AND iptvi.store_id is NULL  AND iptvi.product_id={$this->getRequest()->getParam('id_product')}  ORDER BY val ASC) as tt ";
                        $sortStoreN = $conn->fetchAll($queryStoreNull);

                        $result1 = array_merge($sortStoreGlobal, $sortStoreN);
                        $sortStore =  array_merge($result1, $sortStoreS);

                        if(count($sortStore)==2 && $sortStore[1]['val']<$sortStore[0]['val']){
                            $sortStore = array_reverse($sortStore);
                        }

                        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                        if (count($sortStore) == 2) {

                            if ($sortStore[0]['store'] == NULL && $sortStore[1]['store'] == NULL) {
                                $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('this_sort')},3)";
                                $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$this->getRequest()->getParam('up')},3)";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['store'] == $this->getRequest()->getParam('id_store') && $sortStore[1]['store'] == $this->getRequest()->getParam('id_store')) {


                                $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')} ";
                                $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);
                            } elseif ($sortStore[0]['store'] != NULL && $sortStore[1]['store'] == NULL) {
                              $sql = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_this')},{$this->getRequest()->getParam('id_product')},{$sortStore[0]['val']},3)";
                              $sql1 = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[1]['val']} WHERE value_id={$this->getRequest()->getParam('prevValue')}";
                                $conn->query($sql);
                                $conn->query($sql1);

                            } else {
                                 $sql = "UPDATE {$resource->getTableName('itoris_product_tabs_value_int')}  set value={$sortStore[0]['val']} WHERE value_id={$this->getRequest()->getParam('thisValue')} ";
                                 $sql1 = "INSERT INTO  {$resource->getTableName('itoris_product_tabs_value_int')}  (store_id,tab_id,product_id,value,attribute_id) VALUES ({$this->getRequest()->getParam('id_store')},{$this->getRequest()->getParam('id_up')},{$this->getRequest()->getParam('id_product')},{$sortStore[1]['val']},3)";

                                $conn->query($sql);
                                $conn->query($sql1);
                            }
                        }
                        $result = $jsonFactory->create();
                        $this->cleanCashe();
                        return $result->setData(['success' => true]);
                    }
        }
        $idProduct=$this->getRequest()->getParam('id');
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getBlock('itoris.global.product.tabs.grid')
            ->setProductId($idProduct)
            ->setUseAjax(true);
        return $resultLayout;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }
}