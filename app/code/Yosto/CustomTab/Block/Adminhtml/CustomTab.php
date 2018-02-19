<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\CustomTab\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomTab extends \Magento\Framework\View\Element\Template
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_logger;
    protected $_collectionFactory;
    protected $_coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * CustomTab constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Registry $_coreRegistry
     * @param PageFactory $resultPageFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
        Registry $_coreRegistry,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $_coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function getAttribute()
    {
        return $collection = $this->_collectionFactory->create()->addFieldToFilter('attribute_code', array(
            array('like' => 'ct%')));
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig
            ->getValue(
                'yosto_custom_tab_configuration/group/status',
                $storeScope
            );
    }
}