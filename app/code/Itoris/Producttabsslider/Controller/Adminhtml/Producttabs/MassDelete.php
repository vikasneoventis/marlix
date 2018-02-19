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
class MassDelete  extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';
    protected function cleanCashe(){

        $cacheFrontendPool = $this->_objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
        foreach($cacheFrontendPool as $cacheFrontend){
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
        }

    }

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
        $count =count($data);
        $data=implode(',',$data);
        $sql = "DELETE `main_table` FROM `{$resource->getTableName('itoris_producttabs_tabs')}` AS `main_table`
                WHERE  `main_table`.`tab_id`  in({$data})";
        $conn->query($sql);
        $this->messageManager->addSuccess(__('A total of %1 tab(s) have been deleted.', $count));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->cleanCashe();
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }
}