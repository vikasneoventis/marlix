<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\AdvancedReport\Controller\Adminhtml\Report\Customer;

use Magento\Reports\Controller\Adminhtml\Report\Customer;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
/**
 * Class Group
 * @package Yosto\AdvancedReport\Controller\Adminhtml\Report\Customer
 */
class Group extends Customer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param PageFactory $pageFactory
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        PageFactory $pageFactory,
        FileFactory $fileFactory
    ) {
        $this->_logger = $logger;
        $this->_resultPageFactory = $pageFactory;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $fileFactory);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_AdvancedReport::customer_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Sales by Customer Group'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_AdvancedReport::customer_group');
    }
}