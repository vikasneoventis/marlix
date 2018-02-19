/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(
[
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'jquery/validate',
    'jquery/ui',
    'jquery/jquery.parsequery'
],
function ($, _, $t, priceUtils) {
    'use strict';

    $.widget('mageworx.optionSwatches', {
        options: {
            hiddenSelectClass: 'mageworx-swatch',
            optionClass: 'mageworx-swatch-option'
        },

        /**
         * Triggers one time at first run (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            this._observeStyleOptions();
            this._grayoutDisabledOptions();
            this._initEventListener();
            this._validateRequiredSwatches();
        },

        /**
         * Observe style changes of select to show/hide swatch divs
         * Example: OptionDependency hides child option - divs must be unchecked and hidden
         */
        _observeStyleOptions: function () {
        
            var self = this,
                target = $('.' + this.options.optionClass).next('select').find('option'),
                observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutationRecord) {
                        self._toggleSwatch($(mutationRecord.target));
                    });
                });

            $.each(target, function (i, e) {
                observer.observe(e, {attributes: true, attributeFilter: ['style']});
            });
        },

        /**
         * Disable swatch image if the corresponding option value is disabled.
         */
        _grayoutDisabledOptions: function () {
        
            var self = this;

            $('.' + this.options.optionClass).each(function () {
                var el = $(this),
                    optionId = el.attr('option-id'),
                    optionValueId = el.attr('option-type-id');
                var optionValue = $('#select_' + optionId + ' option[value="' + optionValueId + '"]');

                if (optionValue.prop('disabled')) {
                    el.addClass('disabled');
                }
            });
        },

        /**
         * Show/hide swatch divs
         */
        _toggleSwatch: function (value) {
        
            var swatch = $('[option-type-id="' + value.val() + '"]');
            swatch.removeClass('selected');
            swatch.css('display', value.css('display'));
        },

        /**
         * Triggers each time when option is updated\changed (from the base.js)
         * @param option
         * @param optionConfig
         * @param productConfig
         * @param base
         */
        update: function update(option, optionConfig, productConfig, base)
        {
            if ($(option).val() == '' && $(option).hasClass(this.options.hiddenSelectClass)) {
                $(option).parent().find('.selected').removeClass('selected');
                $(option).parents('.field').find('label').parent().find('span#value').html('');
            }
        },

        /**
         * Initialize event listener for swatch div's click
         */
        _initEventListener: function () {
        
            var self = this;

            $('body').on('click', '.' + this.options.optionClass, function () {
                self._onClick(this);
            });
        },

        /**
         * Click event for swatch div
         * Process all needed actions for hidden select
         * @param option
         */
        _onClick: function (option) {
        
            if ($(option).hasClass('disabled')) {
                return;
            }

            var optionId = $(option).attr('option-id');
            var optionValueId = $(option).attr('option-type-id');
            var select = $('#select_' + optionId);
            var selectOptions = $('#select_' + optionId + ' option');
            if (!selectOptions) {
                return;
            }

            if ($(option).parents('.field').find('label').parent().find('span#value').length <= 0) {
                $(option).parents('.field').find('label').after('<span id="value"></span>');
            }
            $(selectOptions).each(function () {
                if ($(this).val() == optionValueId) {
                    if ($(option).hasClass('selected')) {
                        $(select).val('');
                        $(option).parents('.field').find('label').parent().find('span#value').html('');
                        $(option).parent().find('.selected').removeClass('selected');
                    } else {
                        $(select).val(optionValueId);
                        var $el = $(option).parents('.field').find('label').parent().find('span#value');
                        $el.html(' - ' + $(option).attr('option-label'));
                        if ($(option).attr('option-price') > 0) {
                            $el.html($el.html() + ' +' + priceUtils.formatPrice($(option).attr('option-price')));
                        }
                        $(option).parent().find('.selected').removeClass('selected');
                        $(option).addClass('selected');
                    }
                    $(select).trigger('change');
                    return;
                }
            });
        },

        /**
         * Validator for required swatch options
         */
        _validateRequiredSwatches: function () {
        
            $('#product_addtocart_form')
                .mage('validation', {ignore: ':hidden:not(.' + this.options.hiddenSelectClass + ')'});
        }
    });

    return $.mageworx.optionSwatches;
}
);
