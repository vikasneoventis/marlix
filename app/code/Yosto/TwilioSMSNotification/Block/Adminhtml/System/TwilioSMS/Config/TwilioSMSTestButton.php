<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Block\Adminhtml\System\TwilioSMS\Config;

/**
 * Class TwilioSMSTestButton
 * @package Yosto\TwilioSMSNotification\Block\Adminhtml\System\TwilioSMS\Config
 */
class TwilioSMSTestButton extends \Magento\Config\Block\System\Config\Form\Field
{
    /** @var UrlInterface */
    protected $_urlBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate
        (
            'Yosto_TwilioSMSNotification::system/twiliosms/config/testSMSButton.phtml'
        );
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'twilioapp_test_result_button',
                'label' => __('Send Test SMS'),
                'onclick' => 'javascript:smsAppTest(); return false;',
            ]
        );

        return $button->toHtml();
    }

    /**
     * @return string
     */
    public function getAdminUrl(){
        return $this->_urlBuilder->getUrl
        (
            'yosto_twiliosmsnotification/test',
            ['store' => $this->_request->getParam('store')]
        );
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}