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
    'Magento_Checkout/js/action/select-billing-address',
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
    selectBillingAddress,
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
                        var dataArray = data.id.split('_');
                        var method = {
                            carrier_code: dataArray[0],
                            method_code: dataArray[1]
                        };
                        kcoShippingMethod(method);
                    },
                    'shipping_address_change': function (data) {
                        if (config.frontEndAddress) {
                            klarna.suspend();
                        }
                        $.post(config.refreshAddressUrl, JSON.stringify([]), function (response) {
                            var shipping = response.shipping;
                            var address = {
                                email: shipping.email,
                                prefix: shipping.prefix,
                                company: shipping.company,
                                firstname: shipping.firstname,
                                lastname: shipping.lastname,
                                street: shipping.street,
                                city: shipping.city,
                                region: shipping.region,
                                regionId: shipping.region_id,
                                regionCode: shipping.region_code,
                                postcode: shipping.postcode,
                                countryId: shipping.country_id,
                                telephone: shipping.telephone
                            };
                            var shippingAddress = newAddress(address);
                            selectShippingAddress(shippingAddress);

                            var billing = response.billing;
                            address = {
                                email: billing.email,
                                prefix: billing.prefix,
                                company: billing.company,
                                firstname: billing.firstname,
                                lastname: billing.lastname,
                                street: billing.street,
                                city: billing.city,
                                region: billing.region,
                                regionId: billing.region_id,
                                regionCode: billing.region_code,
                                postcode: billing.postcode,
                                countryId: billing.country_id,
                                telephone: billing.telephone
                            };
                            var billingAddress = newAddress(address);
                            selectBillingAddress(billingAddress);

                            var method = config.shippingMethod;
                            if (quote.shippingMethod()) {
                                method = quote.shippingMethod();
                            }
                            kcoShippingMethod(method);
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
