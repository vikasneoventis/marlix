<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\Test;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Yosto\TwilioSMSNotification\Helper\Constant;

/**
 * Class Index
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\Test
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Yosto\Smtp\Helper\Data
     */
    protected $_dataHelper;

    protected $_twilioSMSFactory;
    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Yosto\Smtp\Helper\Data $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Yosto\TwilioSMSNotification\Helper\Data $dataHelper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest();
        $sid = $request->getPost('sid');
        $token = $request->getPost('token');
        $twilioPhone = $request->getPost('phone');
        $your_phone = $request->getPost('your_phone');

        if (!$request->getParam('store', false)) {
            if (empty($sid) || empty($token)) {
                $this->getResponse()->setBody(__('Please enter a valid sid and token'));
                return;
            }
        }
        $client = new \Services_Twilio($sid, $token);
        try {
            $message = $client->account->messages->create(array(
                Constant::FROM => $twilioPhone, // From a valid Twilio number
                Constant::TO => $your_phone, // Text this number
                Constant::BODY => Constant::TEST_MESSAGE,
            ));
            $result = __('Sent... Please check your message');
        } catch (\Services_Twilio_RestException $e) {
            $result = __('Message can not be sent, please check your config or phone number');
        }


        $this->getResponse()->setBody($this->makeClickableLinks($result));
    }

    /**
     * Make link clickable
     * @param string $s
     * @return string
     */
    public function makeClickableLinks($s)
    {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_TwilioSMSNotification');
    }
}