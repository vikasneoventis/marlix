/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Trollweb_Bring/js/model/shipping-rates-validator/bringpickup',
        'Trollweb_Bring/js/model/shipping-rates-validation-rules/bringpickup'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        bringShippingRatesValidator,
        bringShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('bringpickup', bringShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('bringpickup', bringShippingRatesValidationRules);
        return Component;
    }
);
