<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Dashboard
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\Report
 */
class Dashboard extends Action
{

    protected $_resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $pageFactory;
    }

    /**
     * Init page, title and set active menu
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_TwilioSMSNotification::report_dashboard');
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(__('SMS General Reports'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Yosto_TwilioSMSNotification::report_dashboard');
    }

}