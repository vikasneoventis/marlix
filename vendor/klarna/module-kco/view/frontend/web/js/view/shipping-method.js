/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/shipping',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/get-totals',
    'Klarna_Kco/js/model/klarna',
    'Klarna_Kco/js/model/config',
    'Klarna_Kco/js/action/select-shipping-method'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    selectShippingMethodAction,
    setShippingInformationAction,
    checkoutData,
    getTotals,
    klarna,
    config,
    kcoShippingMethod
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Klarna_Kco/shipping-method'
        },
        visible: ko.observable(!config.frontEndShipping),

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this;
            this._super();
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingInformation()) {
                setShippingInformationAction();
            }
        },

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            kcoShippingMethod(shippingMethod);
            return true;
        }

    });
});
