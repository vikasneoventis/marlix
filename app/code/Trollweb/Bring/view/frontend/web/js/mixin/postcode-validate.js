define([
    'jquery',
    'uiRegistry',
    'mage/translate'
], function ($, uiRegistry, $t) {
    'use strict';

    return function(model) {
        // Return original model unless postcode lookup is enabled
        if (!window.checkoutConfig.bring.postcodeLookup.isEnabled) {
            return model;
        }

        var postcodeElement = null;

        // Fetch postcode element
        uiRegistry.async('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')(function(element, force, delay) {
            postcodeElement = element;
        });

        var originalValidator = model.postcodeValidation;

        model.postcodeValidation = function() {
            // Validate with built-in validator first and return if it does not validate
            var ok = originalValidator();
            if (!ok) {
                return ok;
            }

            if (postcodeElement == null || postcodeElement.value() == null) {
                return true;
            }

            var postcode = postcodeElement.value();
            var countryId = $('select[name="country_id"]').val();

            if (!countryId || !postcode) {
                return true;
            }

            var params = $.param({
                form_key: $.mage.cookies.get('form_key'),
                country: countryId,
                postcode: postcode,
            });

            var url = window.checkoutConfig.bring.postcodeLookup.url + "?" + params;

            var req = $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                context: this,
            });

            req.done(function(res) {
                if (res.valid === false) {
                    postcodeElement.warn($t("Ugyldig postnummer"));
                }
            });

            return true;
        }

        // Return modified model
        return model;
    };
});
