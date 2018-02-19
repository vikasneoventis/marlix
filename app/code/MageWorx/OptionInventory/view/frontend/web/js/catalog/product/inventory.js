/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'priceUtils',
    'priceBox',
    'jquery/ui'
], function ($, utils) {
    'use strict';

    $.widget('mageworx.optionInventory', {
        options: {
            optionConfig: {}
        },

        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            var form = base.element,
                options = $('.product-custom-option', form);

            self._applyOptionNodeFix(options, base);
        },

        update: function update(option, optionConfig, productConfig, base)
        {
            return;
        },

        _applyOptionNodeFix: function applyOptionNodeFix(options, base)
        {
            var config = base.options;
            options.filter('select').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element);

                if ($element.hasClass('datetime-picker')) {
                    return true;
                }

                var optionConfig = config.optionConfig && config.optionConfig[optionId];

                $element.find('option').each(function (idx, option) {
                    var $option,
                        optionValue,
                        stockMessage;

                    $option = $(option);
                    optionValue = $option.val();

                    if (!optionValue && optionValue !== 0) {
                        return;
                    }

                    stockMessage = optionConfig[optionValue] && optionConfig[optionValue].stockMessage;

                    if (optionConfig[optionValue].stockMessage !== undefined) {
                        $option.text($option.text() + ' ' + stockMessage);
                    }
                });
            });
        },

    });

    return $.mageworx.optionInventory;

});
