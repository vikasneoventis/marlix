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
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'ko',
    'mage/utils/wrapper',
    'Klarna_Kco/js/model/klarna',
    'Klarna_Kco/js/model/message',
    'Klarna_Kco/js/model/config'
], function ($, ko, wrapper, klarna, message, config) {
    'use strict';

    return function (getTotals) {
        return wrapper.wrap(getTotals, function (originalAction) {
            klarna.suspend();
            originalAction();
            $.post(config.reloadUrl, function (response) {
                message.saveResponse(response);
            })
                .fail(function () {
                    location.href = config.failureUrl;
                })
                .done(function () {
                    klarna.resume();
                });
        });
    }
});
