/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define(['ko'], function (ko) {
    'use strict';
    var accept_terms_url = window.checkoutConfig.klarna.dach.accept_terms_url;
    var user_terms_url = window.checkoutConfig.klarna.dach.user_terms_url;
    var prefill_notice_enabled = ko.observable(window.checkoutConfig.klarna.dach.prefill_notice_enabled);
    return {
        accept_terms_url: accept_terms_url,
        user_terms_url: user_terms_url,
        prefill_notice_enabled: prefill_notice_enabled
    };
});
