/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'ko',
    'uiComponent',
    'underscore',
    'jquery',
    'Klarna_Dach/js/model/config',
    'mage/translate'
], function (
    ko,
    Component,
    _,
    $,
    config,
    $t
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Klarna_Dach/prefill_notice'
        },
        isVisible: ko.computed(function () {
            return config.prefill_notice_enabled()
        }),
        showTerms: ko.observable(false),
        toggleTerms: function () {
            this.showTerms(!this.showTerms());
        },
        getAcceptTerms: function () {
            location.href = config.accept_terms_url;
        },
        getUserTermsText: function () {
            var notice = $('#notice_terms_hidden').text();
            return notice.replace('%1', config.user_terms_url);
        },

        initialize: function () {
            this._super();
            return this;
        }
    });
});
