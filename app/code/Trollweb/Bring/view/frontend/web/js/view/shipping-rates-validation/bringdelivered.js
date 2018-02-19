/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Trollweb_Bring/js/model/shipping-rates-validator/bringdelivered',
        'Trollweb_Bring/js/model/shipping-rates-validation-rules/bringdelivered'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        bringShippingRatesValidator,
        bringShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('bringdelivered', bringShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('bringdelivered', bringShippingRatesValidationRules);
        return Component;
    }
);
