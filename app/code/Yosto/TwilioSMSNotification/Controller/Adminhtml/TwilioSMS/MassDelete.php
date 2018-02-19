<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

use Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS;

/**
 * Class MassDelete
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\TwilioSMS
 */
class MassDelete extends TwilioSMS
{
    public function execute()
    {
        $twilioSMSIds = $this->getRequest()->getParam('twiliosms');
        $model = $this->_twilioSMSFactory->create();
        $isSuccess = true;
        foreach ($twilioSMSIds as $twilioSMSId) {
            try {
                $model->load($twilioSMSId);
                $model->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $isSuccess = false;
            }
        }
        if ($isSuccess == true) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($twilioSMSIds))
            );
        }
        $this->_redirect('*/*/index');
    }
}