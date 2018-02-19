<?php

namespace Trollweb\Bring\Block\Adminhtml\Form\Field;

use Trollweb\Bring\Model\Carrier\Delivered;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class DeliveredMethods extends Select
{
    private $deliveredModel;

    public function __construct(Context $context, Delivered $deliveredModel, array $data = [])
    {
        parent::__construct($context, $data);
        $this->deliveredModel = $deliveredModel;
    }
    
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->deliveredModel->getAllMethods());
        }
        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
