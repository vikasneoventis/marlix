<?php

namespace Trollweb\Bring\Block\Adminhtml\Form\Field;

use Trollweb\Bring\Model\Carrier\Pickup;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class PickupMethods extends Select
{
    private $pickupModel;

    public function __construct(Context $context, Pickup $pickupModel, array $data = [])
    {
        parent::__construct($context, $data);
        $this->pickupModel = $pickupModel;
    }
    
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->pickupModel->getAllMethods());
        }
        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
