<?php

namespace BoostMyShop\BarcodeLabel\Block\System\Config\System;

class Initialize extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getButtonHtml();
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'barcodelabel_initialize',
                'label' => __('Initialize'),
                'onclick' => "setLocation('".$this->getUrl('barcodelabel/configuration/initialize')."');"
            ]
        );

        return $button->toHtml();
    }

}
