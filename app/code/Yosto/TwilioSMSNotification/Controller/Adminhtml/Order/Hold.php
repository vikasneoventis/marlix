<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\TwilioSMSNotification\Controller\Adminhtml\Order;

/**
 * Class Hold
 * @package Yosto\TwilioSMSNotification\Controller\Adminhtml\Order
 */
class Hold extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    /**
     * Hold order
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->isValidPostRequest()) {
            $this->messageManager->addError(__('You have not put the order on hold.'));
            return $resultRedirect->setPath('sales/*/');
        }
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->orderManagement->hold($order->getEntityId());
                $this->messageManager->addSuccess(__('You put the order on hold.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You have not put the order on hold.'));
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
