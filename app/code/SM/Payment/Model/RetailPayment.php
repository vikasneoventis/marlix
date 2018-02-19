<?php
namespace SM\Payment\Model;
class RetailPayment extends \Magento\Framework\Model\AbstractModel implements RetailPaymentInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_payment';

    protected function _construct()
    {
        $this->_init('SM\Payment\Model\ResourceModel\RetailPayment');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
