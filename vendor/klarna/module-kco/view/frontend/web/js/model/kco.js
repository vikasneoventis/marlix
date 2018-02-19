/**
 * (c) Klarna Bank AB (publ)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   Klarna
 * @package    Klarna_Kco
 * @author     Joe Constant <joe.constant@klarna.com>
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/new-customer-address',
    'Magento_Checkout/js/action/get-totals',
    'mage/utils/objects',
    'Klarna_Kco/js/model/config',
    'Klarna_Kco/js/model/klarna',
    'Klarna_Kco/js/model/message',
    'Klarna_Kco/js/action/select-shipping-method'
], function (
    ko,
    $,
    quote,
    selectShippingMethodAction,
    selectPaymentMethodAction,
    selectShippingAddress,
    setShippingInformation,
    newAddress,
    getTotals,
    mageUtils,
    config,
    klarna,
    message,
    kcoShippingMethod
) {
    'use strict';
    return {
        attachEvents: function () {
            var self = this;
            window._klarnaCheckout(function (api) {
                api.on({
                    'change': function (data) {
                        if (!config.frontEndAddress && !data.given_name) {
                            $.post(config.saveUrl, JSON.stringify(data));
                            getTotals([]);
                        }
                    },
                    'shipping_option_change': function (data) {
                        getTotals([]);
                    },
                    'shipping_address_change': function (data) {
                        if (config.frontEndAddress) {
                            klarna.suspend();
                        }
                        if (!config.frontEndShipping) {
                            $.post(config.countryLookup, JSON.stringify(data), function (response) {
                                var address = {
                                    country_id: response.country_id,
                                    region: response.region,
                                    postcode: response.postal_code,
                                    email: response.email
                                };
                                selectShippingAddress(newAddress(address));
                                var method = config.shippingMethod;
                                if (quote.shippingMethod()) {
                                    method = quote.shippingMethod();
                                }
                                kcoShippingMethod(method);
                                setShippingInformation();
                                getTotals([]);
                            })
                                .fail(self.ajaxFailure);
                        }
                        $.post(config.addressUrl, JSON.stringify(data), function (response) {
                            message.saveResponse(response);
                            getTotals([]);
                        })
                            .fail(self.ajaxFailure);
                    }
                });
            });
        },

        selectShippingMethod: function () {
            var method = config.shippingMethod;
            if (window.checkoutConfig.selectedShippingMethod) {
                method = window.checkoutConfig.selectedShippingMethod;
            }
            selectShippingMethodAction(method);
        },

        setPaymentMethod: function () {
            selectPaymentMethodAction(config.paymentMethod);
        },

        ajaxFailure: function () {
            location.href = config.failureUrl;
        }
    }
});
