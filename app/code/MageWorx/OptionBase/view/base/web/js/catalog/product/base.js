/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'uiRegistry',
    'underscore',
    'mage/template',
    'jquery/ui'
], function ($, utils, registry, _, mageTemplate) {
    'use strict';

    $.widget('mageworx.optionBase', {
        options: {
            optionConfig: {},
            productConfig: {},
            productMainInfoSelector: '.product-info-main',
            extendedOptionsConfig: {},
            priceHolderSelector: '.price-box',
            dateDropdownsSelector: '[data-role=calendar-dropdown]',
            optionsSelector: '.product-custom-option',
            optionHandlers: {},
            optionTemplate: '<%= data.label %>' +
            '<% if (data.finalPrice.value) { %>' +
            ' +<%- data.finalPrice.formatted %>' +
            '<% } %>',
            controlContainer: 'dd',
            priceTemplate: '<span class="price"><%- data.formatted %></span>',
            localePriceFormat: {},
            productFinalPriceExclTax: 0.0,
            productRegularPriceExclTax: 0.0,
            productFinalPriceInclTax: 0.0,
            productRegularPriceInclTax: 0.0,
            priceDisplayMode: 0,
            catalogPriceContainsTax: false
        },
        updaters: {},

        /**
         * @private
         */
        _init: function initPriceBundle() {
            $(this.options.optionsSelector, this.getFormElement()).trigger('change');

            var self = this;
            _.each( this.updaters, function(value, key) {
                try {
                    self.triggerAfterInitPrice(self.getUpdater(key));
                } catch (e) {
                    console.log('Error:');
                    console.log(e);
                }
            });
        },

        _create: function create() {
            var self = this;
            $(document).ready(function() {

                registry.set('mageworxOptionBase', self);

                // Get existing updaters from registry
                var updaters = registry.get('mageworxOptionUpdaters');
                if (!updaters) {
                    updaters = {};
                }

                // Add each updater
                for (var name in updaters) {
                    if (!updaters.hasOwnProperty(name)) {
                        continue;
                    }
                    self.addUpdater(name, updaters[name])
                }

                // Bind option change event listener
                self.addOptionChangeListeners();
                $('#product-options-wrapper').show();
            });
        },

        /**
         * Add updater to the collection
         * Trigger first run of updater
         * @param name
         * @param updater
         */
        addUpdater: function addUpdater(name, updater) {
            var updaterInstance;
            try {
                updaterInstance = this.getUpdater(name);
            } catch (e) {
                updaterInstance = null;
            }

            if (updaterInstance) {
                return;
            }

            this.updaters[name] = updater;
            try {
                updaterInstance = this.getUpdater(name);
                this.runUpdater(updaterInstance);
            } catch (e) {
                console.log('Error:');
                console.log(e);
            }
        },

        /**
         * Get updater by name from collection
         * @param name
         * @returns {*}
         */
        getUpdater: function (name) {
            if (_.isUndefined(this.updaters[name])) {
                throw 'Undefined updater with name: ' + name;
            }

            return this.updaters[name];
        },

        /**
         * Run all updaters (first run)
         */
        runUpdater: function (updater) {
            var handler = updater.firstRun;
            if (typeof handler != 'undefined' && handler && handler instanceof Function) {
                handler.apply(updater, [this.options.optionConfig, this.options.productConfig, this, updater]);
            }
        },

        /**
         * Run all updaters (after init price)
         */
        triggerAfterInitPrice: function (updater) {
            var handler = updater.afterInitPrice;
            if (typeof handler != 'undefined' && handler && handler instanceof Function) {
                handler.apply(updater, [this.options.optionConfig, this.options.productConfig, this, updater]);
            }
        },

        /**
         * Add event listener on each option change (for updaters)
         */
        addOptionChangeListeners: function addListeners() {
            $(this.options.optionsSelector, this.getFormElement()).on('change', this.optionChanged.bind(this));
        },

        /**
         * Custom behavior on getting options:
         * now widget able to deep merge accepted configuration with instance options.
         * @param  {Object}  options
         * @return {$.Widget}
         * @private
         */
        _setOptions: function setOptions(options) {
            $.extend(true, this.options, options);
            return this._super(options);
        },

        /**
         * Find corresponding form element in DOM
         * Throws exception when form is not found
         * @returns {$}
         */
        getFormElement: function () {
            var $form;
            if (this.element.is('form')) {
                $form = this.element;
            } else {
                $form = this.element.closest('form');
            }

            if ($form.length == 0) {
                throw 'Invalid or empty form element';
            }

            return $form;
        },

        /**
         * Custom option change-event handler
         * @param {Event} event
         * @private
         */
        optionChanged: function onOptionChanged(event) {
            var option = $(event.target);
            option.data('optionContainer', option.closest(this.options.controlContainer));

            $.each(this.updaters, function (i, e) {
                var handler = e.update;
                if (handler && handler instanceof Function) {
                    handler.apply(e, [option, this.options.optionConfig, this.options.productConfig, this]);
                }
            }.bind(this));

            $.each(this.updaters, function (i, e) {
                var handler = e.applyChanges;
                if (handler && handler instanceof Function) {
                    handler.apply(e, [this]);
                }
            }.bind(this));
        },

        /**
         * Set product final price
         * @param finalPrice
         */
        setProductFinalPrice: function (finalPrice) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $(config.productMainInfoSelector).find('[data-price-type="finalPrice"]'),
                templateData = {};

            if (_.isUndefined($pc)) {
                return;
            }

            if (finalPrice <= 0) {
                if (this.getPriceDisplayMode() == 1) {
                    finalPrice = this.options.productFinalPriceExclTax;
                } else {
                    finalPrice = this.options.productFinalPriceInclTax;
                }
            }

            template = mageTemplate(template);
            templateData.data = {
                value: finalPrice,
                formatted: utils.formatPrice(finalPrice, format)
            };

            $pc.hide();
            setTimeout(function () {
                $pc.html(template(templateData));
                $pc.fadeIn(500);
            }, 110)
        },

        setProductPriceExclTax: function (priceExcludeTax) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $(config.productMainInfoSelector).find('[data-price-type="basePrice"]'),
                templateData = {};

            if (_.isUndefined($pc)) {
                return;
            }

            if (priceExcludeTax <= 0) {
                priceExcludeTax = this.options.productFinalPriceExclTax;
            }

            template = mageTemplate(template);
            templateData.data = {
                value: priceExcludeTax,
                formatted: utils.formatPrice(priceExcludeTax, format)
            };

            $pc.hide();
            setTimeout(function () {
                $pc.html(template(templateData));
                $pc.fadeIn(500);
            }, 110)
        },

        /**
         * Set product regular price
         * @param regularPrice
         */
        setProductRegularPrice: function (regularPrice) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $(config.productMainInfoSelector).find('[data-price-type="oldPrice"]'),
                templateData = {};

            if (_.isUndefined($pc)) {
                return;
            }

            if (regularPrice <= 0) {
                if (this.getPriceDisplayMode() == 1) {
                    regularPrice = this.options.productRegularPriceExclTax;
                } else {
                    regularPrice = this.options.productRegularPriceInclTax;
                }
            }

            template = mageTemplate(template);
            templateData.data = {
                value: regularPrice,
                formatted: utils.formatPrice(regularPrice, format)
            };

            $pc.hide();
            setTimeout(function () {
                $pc.html(template(templateData));
                $pc.fadeIn(500);
            }, 110)
        },

        /**
         * Check by the option id is it an one-time option
         * @param optionId
         * @returns {boolean}
         */
        isOneTimeOption: function (optionId) {
            var config = this.options;

            return config.extendedOptionsConfig &&
                config.extendedOptionsConfig[optionId] &&
                config.extendedOptionsConfig[optionId]['one_time'] &&
                config.extendedOptionsConfig[optionId]['one_time'] != '0';
        },

        /**
         * Get summary price from all selected options
         *
         * @returns {number}
         */
        calculateSelectedOptionsPrice: function (withTax) {
            var self = this,
                form = this.getFormElement(),
                options = $(this.options.optionsSelector, form),
                config = this.options,
                processedDatetimeOptions = [],
                price = 0;

            options.filter('select').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    values = $element.val();

                if (_.isUndefined(values) || !values) {
                    return;
                }

                if (!Array.isArray(values)) {
                    values = [values];
                }

                $(values).each(function (i, e) {
                    if (_.isUndefined(optionConfig[e])) {
                        if (_.isUndefined(optionConfig.prices)) {
                            return;
                        }

                        var dateDropdowns = $element.parent().find(self.options.dateDropdownsSelector);
                        if (_.isUndefined(dateDropdowns)) {
                            return;
                        }

                        if ($element.closest('.field').css('display') == 'none') {
                            $element.val('');
                            return;
                        }

                        var optionConfigCurrent = self.getDateDropdownConfig(optionConfig, dateDropdowns);
                        if (_.isUndefined(optionConfigCurrent.prices) ||
                            $.inArray(optionId, processedDatetimeOptions) != -1) {
                            return;
                        }
                        processedDatetimeOptions.push(optionId);
                    } else {
                        var optionConfigCurrent = optionConfig[e];
                    }

                    var qty = !_.isUndefined(optionConfigCurrent['qty']) ? optionConfigCurrent['qty'] : 1;
                    if (withTax) {
                        price += parseFloat(optionConfigCurrent.prices.finalPrice.amount) * qty;
                    } else {
                        price += parseFloat(optionConfigCurrent.prices.basePrice.amount) * qty;
                    }
                });
            });

            options.filter('input[type="radio"], input[type="checkbox"]').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    value = $element.val();

                if (!$element.is(':checked')) {
                    return;
                }

                if (typeof value == 'undefined' || !value) {
                    return;
                }

                var qty = typeof optionConfig[value]['qty'] != 'undefined' ? optionConfig[value]['qty'] : 1;
                if (withTax) {
                    price += parseFloat(optionConfig[value].prices.finalPrice.amount) * qty;
                } else {
                    price += parseFloat(optionConfig[value].prices.basePrice.amount) * qty;
                }
            });

            options.filter('input[type="text"], textarea, input[type="file"]').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    value = $element.val();

                if (typeof value == 'undefined' || !value) {
                    return;
                }

                if ($element.closest('.field').css('display') == 'none') {
                    $element.val('');
                    return;
                }

                var qty = typeof optionConfig['qty'] != 'undefined' ? optionConfig['qty'] : 1;
                if (withTax) {
                    price += parseFloat(optionConfig.prices.finalPrice.amount) * qty;
                } else {
                    price += parseFloat(optionConfig.prices.basePrice.amount) * qty;
                }
            });

            return price;
        },

        /**
         * Get price from html
         *
         * @param element
         * @returns {number}
         */
        getPriceFromHtmlElement: function getPrice(element) {
            var pricePattern = this.options.localePriceFormat,
                ds = pricePattern.decimalSymbol,
                gs = pricePattern.groupSymbol,
                pf = pricePattern.pattern,
                ps = pricePattern.priceSymbol,
                price = 0,
                html = $(element).text(),
                priceCalculated;

            priceCalculated = parseFloat(html.replace(new RegExp("'\'" + gs, 'g'), '')
                .replace(new RegExp("'\'" + ds, 'g'), '.')
                .replace(/[^0-9\.,]/g, ''));

            if (priceCalculated) {
                price = priceCalculated;
            }

            return price;
        },

        getOptionHtmlById: function (optionId) {
            return $(this.options.optionsSelector + '[name^="options[' + optionId + ']"]', this.getFormElement())
                .first()
                .closest('.field[option_id]');
        },

        /**
         * Check is product catalog price already contains tax
         * @returns {number}
         */
        isPriceWithTax: function () {
            return this.toBoolean(this.options.catalogPriceContainsTax);
        },

        /**
         * Get price display mode for prices on the product view page:
         * 1 - without tax
         * 2 - with tax
         * 3 - both (with and without tax)
         * @returns {number}
         */
        getPriceDisplayMode: function () {
            return parseInt(this.options.priceDisplayMode);
        },

        /**
         * Convert value to the boolean type
         * @param value
         * @returns {boolean}
         */
        toBoolean: function (value) {
            return !(value == 0 ||
            value == "0" ||
            value == false);
        },

        /**
         * Parse option ID from the data-selector attribute of the option
         * @param $option
         * @returns {int|NaN}
         */
        getOptionId: function ($option) {
            //compatibility with ie11
            if (navigator.userAgent.indexOf('rv:11') == -1) {
                var regExp = /(options\[){1}(\d+)+(\]){1}/;
                var re = new RegExp(regExp.source, 'g');
            } else {
                var re = new RegExp("/(options\[){1}(\d+)+(\]){1}/", "g");
            }
            re.test($option.attr('data-selector'));

            return parseInt(RegExp.$2);
        },


        getDateDropdownConfig: function (optionConfig, siblings)
        {
            var isNeedToUpdate = true;

            siblings.each(function (index, el) {
                isNeedToUpdate = isNeedToUpdate && !!$(el).val();
            });

            return isNeedToUpdate ? optionConfig : {};
        }
    });

    return $.mageworx.optionBase;
});