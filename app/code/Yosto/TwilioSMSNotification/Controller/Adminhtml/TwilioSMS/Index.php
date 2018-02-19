<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

use Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

/**
 * Class Index
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS
 */
class Index extends TwilioSMS
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_TwilioSMSNotification::commonlogs');
        $resultPage->getConfig()->getTitle()->prepend(__('SMS Logs'));
        $resultPage->addBreadcrumb(__('SMS Logs'), __('SMS Logs'));
        return $resultPage;
    }
}