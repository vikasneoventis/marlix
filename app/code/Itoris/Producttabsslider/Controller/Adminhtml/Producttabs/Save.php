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
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';

    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context

    ) {
        parent::__construct($context);

    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }
    
    protected function cleanCashe(){

                $cacheFrontendPool = $this->_objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
                foreach($cacheFrontendPool as $cacheFrontend){
                $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
                }

    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        /*if(isset($data['content'])){
            $data['content'] = str_replace('\\','\\\\',$data['content']);
            $data['content']=addcslashes($data['content'],"'");
        }*/
        if(!isset($data['tab_id']) && !isset($data['group']))
            $data['group']=explode(',',$data['all_group']);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeManager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $id = $storeManager->getStore()->getId();
        $jsonFactory = $this->_objectManager->create('\Magento\Framework\Controller\Result\JsonFactory');
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();

        if ($data && isset($data['tab_id']) && !isset($data['prod_id'])) {
                $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                if(!isset($data['store_id'])) {
                    $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                    if(isset($data['group']))
                    $data['group']=implode(',',$data['group']);
                    $conn = $resource->getConnection();
                    $sql = "UPDATE `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_varchar')}` AS `iptv1` ON main_table.tab_id = iptv1.tab_id AND iptv1.attribute_id=1
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi2` ON main_table.tab_id = iptvi2.tab_id AND iptvi2.attribute_id=2
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi4` ON main_table.tab_id = iptvi4.tab_id AND iptvi4.attribute_id=4
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi3` ON main_table.tab_id = iptvi3.tab_id AND iptvi3.attribute_id=3
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `iptvi5` ON main_table.tab_id = iptvi5.tab_id AND iptvi5.attribute_id=5
                INNER JOIN `{$resource->getTableName('itoris_product_tabs_value_text')}` AS `iptvi6` ON main_table.tab_id = iptvi6.tab_id AND iptvi6.attribute_id=6
                INNER JOIN {$resource->getTableName('customer_group')} AS `cg` ON find_in_set(cg.customer_group_id,iptvi6.value)
                SET iptv1.value={$conn->quote($data['label'])},iptvi2.value={$data['is_active']},iptvi4.value={$conn->quote($data['content'])} , iptvi5.value={$data['show_purchased']} , iptvi6.value='{$data['group']}'
                WHERE (iptv1.product_id IS NULL AND iptvi2.product_id IS NULL AND iptvi5.product_id IS NULL AND iptvi3.product_id IS NULL AND iptvi4.product_id IS NULL AND iptvi6.product_id IS NULL AND iptv1.store_id IS NULL AND iptvi2.store_id IS NULL AND iptvi5.store_id IS NULL AND iptvi3.store_id IS NULL AND iptvi4.store_id IS NULL AND iptvi6.store_id IS NULL) AND `main_table`.`tab_id` = ".$data['tab_id'];
                        $conn->query($sql);
                        if($this->getRequest()->getParam('back')=='edit'){
                            $this->cleanCashe();
                            return $resultRedirect->setPath('*/*/edit', ['id' => $data['tab_id']]);

                        }
                    $this->cleanCashe();
                        return $resultRedirect->setPath('*/*/');

                }else{
                    $explodes=[];
                    $attrStore=$data['storeAttrs'];
                    $setAlias=['label'=>'iptv1','is_active'=>'iptvi2','content'=>'iptvi4','show_purchased'=>'iptvi5','group'=>'iptvi6'];

                    $sqlUpdate=" update `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`";
                    $length=strlen($sqlUpdate);
                    $set='SET ';
                    $insert='';
                    $joinUpdate='';
                    $attrAray=[];
                    $delete='';
                    $tableDeleTe=[];
                    $tableString='';
                    $criteriaReference='';
                    $deleteWhere='WHERE ';
                    $where=' WHERE( ';
                    $setArrayField=['label'=>"{$resource->getTableName('itoris_product_tabs_value_varchar')}",'is_active'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'content'=>"{$resource->getTableName('itoris_product_tabs_value_text')}",'show_purchased'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'group'=>"{$resource->getTableName('itoris_product_tabs_value_text')}"];
                    $setArrayJoin=['label'=>1,'is_active'=>2,'content'=>4,'show_purchased'=>5,'group'=>6];
                    $arrTable=[];
                    $stringAlias='';
                    $i=0;
                    $oneTable='';
                    $attrStore=explode(',',$attrStore);
                    foreach($attrStore as $atrS){
                        $explodes=explode(':',$atrS);

                        if(!empty($explodes[1])){
                            if($explodes[0]=='group' && isset($data['group'])){
                                $data['group']=implode(',',$data['group']);

                        }

                            if(isset($data[$explodes[0]])) {
                                $where = $where . " {$setAlias[$explodes[0]]}.product_id IS NULL AND {$setAlias[$explodes[0]]}.store_id='{$explodes[1]}' AND ";
                                $set = $set . " {$setAlias[$explodes[0]]}.value={$conn->quote($data[$explodes[0]])}, ";

                                $sqlUpdate = $sqlUpdate . ' ' . " INNER JOIN {$setArrayField[$explodes[0]]} AS {$setAlias[$explodes[0]]}  ON main_table.tab_id ={$setAlias[$explodes[0]]}.tab_id AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]} ";
                            }else{

                                    if(count($tableDeleTe)==0){
                                        $tableDeleTe[] = $setAlias[$explodes[0]];
                                        $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                                        $oneTable=" FROM {$setArrayField[$explodes[0]]} as {$setAlias[$explodes[0]]}";
                                        $delete="DELETE";
                                        $deleteWhere=$deleteWhere." ({$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}
                                        AND {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.store_id ={$data['store_id']}) AND {$setAlias[$explodes[0]]}.product_id IS NULL ";
                                    }else{
                                        $tableDeleTe[] = $setAlias[$explodes[0]];
                                        $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                                        $tableString=$tableString.' INNER JOIN '.$setArrayField[$explodes[0]]." as {$setAlias[$explodes[0]]}  ON {$tableDeleTe[$i-1]}.tab_id ={$setAlias[$explodes[0]]}.tab_id AND {$setAlias[$explodes[0]]}.product_id IS NULL AND  {$setAlias[$explodes[0]]}.store_id={$data['store_id']} AND  {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}";
                                    }


                                    $i++;

                            }

                        }else{
                            if(isset($data[$explodes[0]])){
                                if($explodes[0]=='group' && isset($data['group'])){
                                    $data['group']=implode(',',$data['group']);

                                }
                                if(in_array($setArrayField[$explodes[0]],$arrTable)){
                                    $attrAray[$setArrayField[$explodes[0]]]= $attrAray[$setArrayField[$explodes[0]]].", ({$data['tab_id']},{$data['store_id']}, {$setArrayJoin[$explodes[0]]},{$conn->quote($data[$explodes[0]])})";
                                }else{
                                    $arrTable[]=$setArrayField[$explodes[0]];
                                    $attrAray[$setArrayField[$explodes[0]]]="INSERT INTO `{$setArrayField[$explodes[0]]}` (`tab_id`, `store_id`, `attribute_id`,`value`) VALUES ({$data['tab_id']},{$data['store_id']}, {$setArrayJoin[$explodes[0]]},{$conn->quote($data[$explodes[0]])}) ";
                                }
                            }
                        }
                    }
                    $set=trim($set,' ');
                    $set=trim($set,',');
                    $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                    $conn = $resource->getConnection();

                    if($length!=strlen($sqlUpdate)) {
                        $sqlUpdate=$sqlUpdate.$set.$where." main_table.tab_id={$data['tab_id']})";
                        $conn->query($sqlUpdate);
                    }

                    if( count($attrAray)>0){
                        foreach($attrAray as $value){
                            $conn->query($value);
                        }

                    }
                    if(count($tableDeleTe)>0){
                        $tableString=trim($tableString,',');
                        $deleteWhere=trim($deleteWhere,'OR ');
                        $stringAlias=trim($stringAlias,',');
                        $conn->query($delete.$stringAlias.' '.$oneTable.$tableString.' '.$deleteWhere);
                    }
                    $this->cleanCashe();
                    if($this->getRequest()->getParam('back')=='edit'){
                        $this->cleanCashe();
                        return $resultRedirect->setPath('*/*/edit', ['id' => $data['tab_id'],'store'=>$data['store_id']]);

                    }
                    return $resultRedirect->setPath('*/*/index', ['id' => $data['tab_id'],'store'=>$data['store_id']]);
                }


            $this->_getSession()->setFormData($data);
            if(isset($data['tab_id']) && !isset($data['store_id']))
            return $resultRedirect->setPath('*/*/edit', ['id' => $data['tab_id']]);
            elseif(isset($data['tab_id']) && isset($data['store_id'])){
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['tab_id'],'store'=>$data['store_id']]);
            }else{
                return $resultRedirect->setPath('*/*/edit');
            }
        }
        elseif((isset($data['tab_id']) && isset($data['prod_id']) && !isset($data['store_id']))){
                $explodes=[];
                $attrStore=$data['prodAttrs'];
                $setAlias=['label'=>'iptv1','is_active'=>'iptvi2','content'=>'iptvi4','show_purchased'=>'iptvi5','group'=>'iptvi6'];
            $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                $sqlUpdate=" update `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`";
                $length=strlen($sqlUpdate);
                $set='SET ';
                $insert='';
                $joinUpdate='';
                $attrAray=[];
                $where=' WHERE( ';
                $setArrayField=['label'=>"{$resource->getTableName('itoris_product_tabs_value_varchar')}",'is_active'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'content'=>"{$resource->getTableName('itoris_product_tabs_value_text')}",'show_purchased'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'group'=>"{$resource->getTableName('itoris_product_tabs_value_text')}"];
                $setArrayJoin=['label'=>1,'is_active'=>2,'content'=>4,'show_purchased'=>5,'group'=>6];
                $arrTable=[];
                $attrStore=explode(',',$attrStore);
                $delete='';
                $tableDeleTe=[];
                $tableString='';
                $deleteWhere='WHERE ';
                $arrTable=[];
                $stringAlias='';
                $i=0;
                $oneTable='';
                foreach($attrStore as $atrS){
                    $explodes=explode(':',$atrS);
                    if(!empty($explodes[1])){
                        if($explodes[0]=='group'){
                            if(isset($data['group']))
                            $data['group']=implode(',',$data['group']);

                        }
                        if(isset($data[$explodes[0]])) {
                            $where=$where."{$setAlias[$explodes[0]]}.product_id='{$explodes[1]}'  AND {$setAlias[$explodes[0]]}.store_id IS NULL AND ";
                            $set=$set." {$setAlias[$explodes[0]]}.value={$conn->quote($data[$explodes[0]])}, ";
                            $sqlUpdate=$sqlUpdate.' '." INNER JOIN {$setArrayField[$explodes[0]]} AS {$setAlias[$explodes[0]]}  ON main_table.tab_id ={$setAlias[$explodes[0]]}.tab_id AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]} ";
                        }else{

                            if(count($tableDeleTe)==0){
                                $tableDeleTe[] = $setAlias[$explodes[0]];
                                $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                                $oneTable=" FROM {$setArrayField[$explodes[0]]} as {$setAlias[$explodes[0]]}";
                                $delete="DELETE";
                                $deleteWhere=$deleteWhere." ({$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}
                                        AND {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.store_id IS NULL AND {$setAlias[$explodes[0]]}.product_id ={$data['prod_id']}) ";
                            }else{
                                $tableDeleTe[] = $setAlias[$explodes[0]];
                                $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                                $tableString=$tableString.' INNER JOIN '.$setArrayField[$explodes[0]]." as {$setAlias[$explodes[0]]}  ON {$tableDeleTe[$i-1]}.tab_id ={$setAlias[$explodes[0]]}.tab_id AND  {$setAlias[$explodes[0]]}.store_id IS NULL AND {$setAlias[$explodes[0]]}.product_id={$data['prod_id']} AND  {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}";
                            }


                            $i++;

                        }
                       }else{
                        if(isset($data[$explodes[0]])){
                            if($explodes[0]=='group'){
                                if(isset($data['group']))
                                $data['group']=implode(',',$data['group']);

                            }

                            if(in_array($setArrayField[$explodes[0]],$arrTable)){
                                $attrAray[$setArrayField[$explodes[0]]]= $attrAray[$setArrayField[$explodes[0]]].", ({$data['tab_id']},{$data['prod_id']}, {$setArrayJoin[$explodes[0]]},'{$data[$explodes[0]]}')";
                            }else{
                                $arrTable[]=$setArrayField[$explodes[0]];
                                $attrAray[$setArrayField[$explodes[0]]]="INSERT INTO `{$setArrayField[$explodes[0]]}` (`tab_id`, `product_id`, `attribute_id`,`value`) VALUES ({$data['tab_id']},{$data['prod_id']}, {$setArrayJoin[$explodes[0]]},{$conn->quote($data[$explodes[0]])}) ";
                            }
                        }
                    }
                }
                $set=trim($set,' ');
                $set=trim($set,',');
                $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
                $conn = $resource->getConnection();
            if(count($tableDeleTe)>0){
                $tableString=trim($tableString,',');
                $deleteWhere=trim($deleteWhere,'OR ');
                $stringAlias=trim($stringAlias,',');
                $conn->query($delete.$stringAlias.' '.$oneTable.$tableString.' '.$deleteWhere);
            }
                if($length!=strlen($sqlUpdate)) {
                    $sqlUpdate=$sqlUpdate.$set.$where." main_table.tab_id={$data['tab_id']})";
                    $conn->query($sqlUpdate);
                }
                if( count($attrAray)>0){
                    foreach($attrAray as $value){
                        $conn->query($value);
                    }

                }

            $this->_getSession()->setFormData($data);
            $result = $jsonFactory->create();
            $this->cleanCashe();
            return $result->setData(['success' => true]);
        }elseif((isset($data['tab_id']) && isset($data['prod_id']) && isset($data['store_id']))){
            $explodes=[];
            $attrStore=$data['storeProdAttrs'];
            $setAlias=['label'=>'iptv1','is_active'=>'iptvi2','content'=>'iptvi4','show_purchased'=>'iptvi5','group'=>'iptvi6'];
            $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
            $sqlUpdate=" update `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`";
            $length=strlen($sqlUpdate);
            $set='SET ';
            $insert='';
            $joinUpdate='';
            $attrAray=[];
            $where=' WHERE( ';
            $setArrayField=['label'=>"{$resource->getTableName('itoris_product_tabs_value_varchar')}",'is_active'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'content'=>"{$resource->getTableName('itoris_product_tabs_value_text')}",'show_purchased'=>"{$resource->getTableName('itoris_product_tabs_value_int')}",'group'=>"{$resource->getTableName('itoris_product_tabs_value_text')}"];
            $setArrayJoin=['label'=>1,'is_active'=>2,'content'=>4,'show_purchased'=>5,'group'=>6];
            $arrTable=[];
            $attrStore=explode(',',$attrStore);
            $delete='';
            $tableDeleTe=[];
            $tableString='';
            $deleteWhere='WHERE ';
            $arrTable=[];
            $stringAlias='';
            $i=0;
            $oneTable='';
            foreach($attrStore as $atrS){
                $explodes=explode(':',$atrS);
                if(!empty($explodes[1]) && !empty($explodes[2])){
                    if($explodes[0]=='group'){
                        if(isset($data['group']))
                        $data['group']=implode(',',$data['group']);

                    }
                    if(isset($data[$explodes[0]])) {
                        $where=$where." {$setAlias[$explodes[0]]}.product_id={$explodes[2]} AND {$setAlias[$explodes[0]]}.store_id='{$explodes[1]}' AND ";
                        $set=$set." {$setAlias[$explodes[0]]}.value='{$data[$explodes[0]]}', ";
                        $sqlUpdate=$sqlUpdate.' '." INNER JOIN {$setArrayField[$explodes[0]]} AS {$setAlias[$explodes[0]]}  ON main_table.tab_id ={$setAlias[$explodes[0]]}.tab_id AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]} ";
                    }else{
                        if(count($tableDeleTe)==0){
                            $tableDeleTe[] = $setAlias[$explodes[0]];
                            $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                            $oneTable=" FROM {$setArrayField[$explodes[0]]} as {$setAlias[$explodes[0]]}";
                            $delete="DELETE";
                            $deleteWhere=$deleteWhere." ({$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}
                                        AND {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.store_id ={$data['store_id']} AND {$setAlias[$explodes[0]]}.product_id ={$data['prod_id']}) ";
                        }else{
                            $tableDeleTe[] = $setAlias[$explodes[0]];
                            $stringAlias=$stringAlias." {$setAlias[$explodes[0]]},";
                            $tableString=$tableString.' INNER JOIN '.$setArrayField[$explodes[0]]." as {$setAlias[$explodes[0]]}  ON {$tableDeleTe[$i-1]}.tab_id ={$setAlias[$explodes[0]]}.tab_id AND {$setAlias[$explodes[0]]}.store_id ={$data['store_id']} AND {$setAlias[$explodes[0]]}.product_id={$data['prod_id']} AND  {$setAlias[$explodes[0]]}.tab_id={$data['tab_id']} AND {$setAlias[$explodes[0]]}.attribute_id={$setArrayJoin[$explodes[0]]}";
                        }


                        $i++;

                    }
                   }else{
                    if(isset($data[$explodes[0]])){
                        if($explodes[0]=='group'){
                            if(isset($data['group']))
                            $data['group']=implode(',',$data['group']);

                        }
                        if(in_array($setArrayField[$explodes[0]],$arrTable)){
                            $attrAray[$setArrayField[$explodes[0]]]= $attrAray[$setArrayField[$explodes[0]]].", ({$data['tab_id']},{$data['store_id']},{$data['prod_id']}, {$setArrayJoin[$explodes[0]]},'{$data[$explodes[0]]}')";
                        }else{
                            $arrTable[]=$setArrayField[$explodes[0]];
                            $attrAray[$setArrayField[$explodes[0]]]="INSERT INTO `{$setArrayField[$explodes[0]]}` (`tab_id`, `store_id`,`product_id`, `attribute_id`,`value`) VALUES ({$data['tab_id']},{$data['store_id']},{$data['prod_id']},{$setArrayJoin[$explodes[0]]},{$conn->quote($data[$explodes[0]])}) ";
                        }
                    }
                }
            }
            $set=trim($set,' ');
            $set=trim($set,',');
            $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
            $conn = $resource->getConnection();
            if($length!=strlen($sqlUpdate)) {
                $sqlUpdate=$sqlUpdate.$set.$where." main_table.tab_id={$data['tab_id']})";
                $conn->query($sqlUpdate);
            }


            if( count($attrAray)>0){
                foreach($attrAray as $value){
                    $conn->query($value);
                }

            }

            if(count($tableDeleTe)>0){

                $tableString=trim($tableString,',');
                $deleteWhere=trim($deleteWhere,'OR ');
                $stringAlias=trim($stringAlias,',');
                $conn->query($delete.$stringAlias.' '.$oneTable.$tableString.' '.$deleteWhere);
            }
            $this->_getSession()->setFormData($data);
            $result = $jsonFactory->create();
            $this->cleanCashe();
            return $result->setData(['success' => true]);
        }
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();
        $sql="SELECT MAX(tab_id) as tabMaxId FROM {$resource->getTableName('itoris_producttabs_tabs')}";

        $maxTabId = $conn->fetchAll($sql);

        if(count($maxTabId)>0){
            $maxTabId=array_shift($maxTabId);
        }

        $tabs = $this->_objectManager->create('Itoris\Producttabsslider\Model\ProductTabs');
        try{
            $tabs->addData(++$maxTabId);
            try{


                $tabs->save();
                if(!isset($data['prod_id'])) {
                    if (isset($data['group']))
                        $data['group'] = implode(',', $data['group']);
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_varchar')} (`tab_id`, `attribute_id`,`value`) VALUE({$tabs->getId()},1,{$conn->quote($data['label'])})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_int')}  (`tab_id`, `attribute_id`,`value`)
                    VALUE({$tabs->getId()},2,{$data['is_active']}),({$tabs->getId()},5,{$data['show_purchased']}),({$tabs->getId()},3,{$data['orderMax']})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_text')}  (`tab_id`, `attribute_id`,`value`) VALUE({$tabs->getId()},4,{$conn->quote($data['content'])}),({$tabs->getId()},6,'{$data['group']}')");
                }elseif(isset($data['prod_id']) && !isset($data['store_id'])){
                    if (isset($data['group']))
                        $data['group'] = implode(',', $data['group']);
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_varchar')} (`tab_id`, `attribute_id`,`product_id`,`value`) VALUE
                                                                             ({$tabs->getId()},1,{$data['prod_id']},{$conn->quote($data['label'])})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_int')}  (`tab_id`, `attribute_id`,`product_id`,`value`)
                    VALUE
                    ({$tabs->getId()},2,{$data['prod_id']},{$data['is_active']}),
                    ({$tabs->getId()},5,{$data['prod_id']},{$data['show_purchased']}),
                    ({$tabs->getId()},3,{$data['prod_id']},{$data['orderMax']})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_text')}  (`tab_id`, `attribute_id`,`product_id`,`value`) VALUE
                   ({$tabs->getId()},4,{$data['prod_id']},{$conn->quote($data['content'])}),
                   ({$tabs->getId()},6,{$data['prod_id']},'{$data['group']}')");
                    $result = $jsonFactory->create();
                    $this->cleanCashe();
                    return $result->setData(['success' => true]);
                }elseif(isset($data['prod_id']) && isset($data['store_id'])){
                    if (isset($data['group']))
                        $data['group'] = implode(',', $data['group']);
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_varchar')} (`tab_id`, `attribute_id`,`product_id`,`store_id`,`value`) VALUE
                                           ({$tabs->getId()},1,{$data['prod_id']},{$data['store_id']},{$conn->quote($data['label'])})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_int')}  (`tab_id`, `attribute_id`,`product_id`,`store_id`,`value`)
                    VALUE
                    ({$tabs->getId()},2,{$data['prod_id']},{$data['store_id']},{$data['is_active']}),
                    ({$tabs->getId()},5,{$data['prod_id']},{$data['store_id']},{$data['show_purchased']}),
                    ({$tabs->getId()},3,{$data['prod_id']},{$data['store_id']},{$data['orderMax']})");
                    $conn->query("INSERT INTO {$resource->getTableName('itoris_product_tabs_value_text')}  (`tab_id`, `attribute_id`,`product_id`,`store_id`,`value`)
                    VALUE({$tabs->getId()},4,{$data['prod_id']},{$data['store_id']},{$conn->quote($data['content'])}),
                    ({$tabs->getId()},6,{$data['prod_id']},{$data['store_id']},'{$data['group']}')");

                    $result = $jsonFactory->create();
                    $this->cleanCashe();
                    return $result->setData(['success' => true]);
                }





            }catch(\Magento\Framework\Exception\LocalizedException $e){
                $tabs->remove();
                $this->messageManager->addError($e->getMessage());
            }

        }catch(\Magento\Framework\Exception\LocalizedException $e){
            $this->messageManager->addError($e->getMessage());
        }
        if($this->getRequest()->getParam('back')=='edit'){
            $this->cleanCashe();
            return $resultRedirect->setPath('*/*/edit',['id' => $tabs->getId()]);

        }
        $this->cleanCashe();
        return $resultRedirect->setPath('*/*/index');

    }
}