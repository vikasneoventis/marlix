<?php

namespace Trollweb\Bring\Block\Adminhtml\Form\Field;

use Trollweb\Bring\Model\Carrier\Pickup;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Checkbox extends \Magento\Framework\View\Element\Template
{

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml()
    {
        return '<input type="checkbox" name="' . $this->getName() . '" id="' . $this->getId() . '" value="1" <%- allow_free_shipping %>>';
    }
}
