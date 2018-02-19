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
    'uiComponent',
    'underscore',
    'jquery',
    'Klarna_Kco/js/model/config',
    'Klarna_Kco/js/model/klarna',
    'Klarna_Kco/js/model/kco',
    'Magento_Checkout/js/model/step-navigator',
    'domReady',
    'Klarna_Kco/js/model/message',
    'mage/translate'
], function (
    ko,
    Component,
    _,
    jQuery,
    config,
    klarna,
    kco,
    stepNavigator,
    domReady,
    message,
    $t
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Klarna_Kco/klarna'
        },
        isVisible: ko.observable(true),
        messages: ko.computed(function () {
            return message.content;
        }),

        initialize: function () {
            this._super();
            stepNavigator.registerStep(
                'klarna_kco',
                null,
                $t('Checkout'),
                this.isVisible,
                _.bind(this.navigate, this),
                100
            );
            kco.selectShippingMethod();
            kco.setPaymentMethod();
            config.enabled = true;

            domReady(function () {
                var checkExist = window.setInterval(function () {
                    if (window._klarnaCheckout) {
                        kco.attachEvents();
                        window.clearInterval(checkExist);
                    }
                }, 1000);
            });
            return this;
        },

        /**
         * The navigate() method is responsible for navigation between checkout step
         * during checkout. You can add custom logic, for example some conditions
         * for switching to your custom step. (This method is required even though it
         * is blank, don't delete)
         */
        navigate: function () {

        },

        navigateToNextStep: function () {
            stepNavigator.next();
        }
    });
});
