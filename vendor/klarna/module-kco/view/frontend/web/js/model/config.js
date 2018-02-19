/**
 *  (c) Klarna Bank AB (publ)
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 * @category   Klarna
 * @package    Klarna_Kco
 * @author     Joe Constant <joe.constant@klarna.com>
 * /
 */
define([], function () {
    'use strict';
    var saveUrl = window.checkoutConfig.klarna.saveUrl;
    var failureUrl = window.checkoutConfig.klarna.failureUrl;
    var reloadUrl = window.checkoutConfig.klarna.reloadUrl;
    var addressUrl = window.checkoutConfig.klarna.addressUrl;
    var methodUrl = window.checkoutConfig.klarna.methodUrl;
    var regionUrl = window.checkoutConfig.klarna.regionUrl;
    var countryLookup = window.checkoutConfig.klarna.countryUrl;
    var messageId = window.checkoutConfig.klarna.messageId;
    var frontEndAddress = window.checkoutConfig.klarna.frontEndAddress;
    var frontEndShipping = window.checkoutConfig.klarna.frontEndShipping;
    var shippingMethod = window.checkoutConfig.klarna.shippingMethod;
    var paymentMethod = window.checkoutConfig.klarna.paymentMethod;
    var enabled = false;
    return {
        enabled: enabled,
        shippingMethod: shippingMethod,
        paymentMethod: paymentMethod,
        frontEndShipping: frontEndShipping,
        frontEndAddress: frontEndAddress,
        messageId: messageId,
        addressUrl: addressUrl,
        methodUrl: methodUrl,
        regionUrl: regionUrl,
        countryLookup: countryLookup,
        reloadUrl: reloadUrl,
        failureUrl: failureUrl,
        saveUrl: saveUrl
    };
});
