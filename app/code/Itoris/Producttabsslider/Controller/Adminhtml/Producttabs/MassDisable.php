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

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

use Itoris\Producttabsslider\Model\ResourceModel\ProductTabs\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDisable
 */
class MassDisable  extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';
    protected function cleanCashe(){

        $cacheFrontendPool = $this->_objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
        foreach($cacheFrontendPool as $cacheFrontend){
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
        }

    }
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();
        $data = $this->getRequest()->getPostValue();
        $data = $data['tab_id'];
        $count=count($data);
        $tabGlobal=[];
        $data=implode(',',$data);
        if($this->getRequest()->getParam('store')==NULL) {
            $sql = "UPDATE  `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `main_table`
                SET main_table.value=0
                WHERE  `main_table`.`tab_id`  in({$data}) AND main_table.attribute_id=2 AND main_table.product_id IS NULL AND main_table.store_id IS NULL";
            $conn->query($sql);

        }else{
            $insert="INSERT INTO {$resource->getTableName('itoris_product_tabs_value_int')} (tab_id,attribute_id,value,store_id) VALUES ";
            $strlen=strlen($insert);
            $slect="SELECT * FROM (SELECT iptvi.store_id as store,iptvi.tab_id as tab FROM {$resource->getTableName('itoris_product_tabs_value_int')} as iptvi
                    WHERE  iptvi.tab_id in({$data})  AND iptvi.attribute_id=2 AND iptvi.product_id IS NULL  AND (iptvi.store_id IS NULL  OR iptvi.store_id = {$this->getRequest()->getParam('store')})  ORDER BY iptvi.store_id DESC ) as tt GROUP BY tab";
            $data=$conn->fetchAll($slect);
            foreach($data as $tab){
               if($tab['store']==NULL){
                   $insert=$insert."({$tab['tab']},2,0,{$this->getRequest()->getParam('store')}), ";
               }else{
                   $tabGlobal[]=$tab['tab'];
               }
            }
            if($strlen!=strlen($insert)){
                $insert=trim($insert,', ');
                $conn->query($insert);
            }
            if(count($tabGlobal)>0){
                $tabGlobal=implode(',',$tabGlobal);
                $sql = "UPDATE  `{$resource->getTableName('itoris_product_tabs_value_int')}` AS `main_table`
                SET main_table.value=0
                WHERE  `main_table`.`tab_id`  in({$tabGlobal}) AND main_table.attribute_id=2 AND main_table.product_id IS NULL AND main_table.store_id={$this->getRequest()->getParam('store')}";
                $conn->query($sql);
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 tab(s) have been disabled.', $count));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->cleanCashe();
        if($this->getRequest()->getParam('store'))
            return $resultRedirect->setPath('*/*/index',['store'=>$this->getRequest()->getParam('store')]);
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }
}