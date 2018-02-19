<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Plugin\Sales\Block\Adminhtml\Order\View;

class InfoPlugin
{
    /**
     * Wrapper around getAddressEditLink() so that we don't allow editing orders paid for using
     * Klarna payment method types
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param callable                                       $proceed
     * @param \Magento\Sales\Model\Order\Address $address
     * @param string $label
     *
     * @return string
     */
    public function aroundGetAddressEditLink(\Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $proceed, $address, $label = '')
    {
        $klarnaMethods = ['klarna_kco', 'klarna_kp'];
        if (in_array($address->getOrder()->getPayment()->getMethod(), $klarnaMethods, true)) {
            return '';
        }
        return $proceed($address, $label);
    }
}
