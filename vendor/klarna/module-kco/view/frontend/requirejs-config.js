/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/get-payment-information': {
                'Klarna_Kco/js/action/override': true
            },
            'Magento_Checkout/js/action/get-totals': {
                'Klarna_Kco/js/action/get-totals': true
            }
        }
    }
};
