<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yosto\TwilioSMSNotification\Model\TwilioSMSFactory;

/**
 * Class TwilioSMS
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml
 */
abstract class TwilioSMS extends Action
{
    protected $_coreRegistry;
    protected $_resultPageFactory;
    protected $_logger;
    protected $_twilioSMSFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        TwilioSMSFactory $twilioSMSFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_twilioSMSFactory = $twilioSMSFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_TwilioSMSNotification::commonlogs');
    }
    public function execute()
    {
        parent::execute();
    }
}