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
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define([
    'jquery',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/get-totals',
    'Klarna_Kco/js/model/klarna',
    'Klarna_Kco/js/model/config'
], function (
    $,
    selectShippingMethodAction,
    checkoutData,
    getTotals,
    klarna,
    config
) {
    "use strict";
    return function (shippingMethod) {
        selectShippingMethodAction(shippingMethod);
        checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
        var data = {
            shipping_method: shippingMethod.carrier_code + '_' + shippingMethod.method_code
        };
        klarna.suspend();
        if (config.frontEndShipping) {
            getTotals([]);
        } else {
            $.post(config.methodUrl, data)
                .done(function () {
                    getTotals([]);
                });
        }
        return true;
    }
});
