/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    $.widget('mageworx.optionDependency', {
        options: {
            dataType: {
                option: 'option_id',
                value: 'option_type_id'
            },
            addToCartSelector: '#product_addtocart_form',
            options: [],
            firstRunProcessed: []
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
            this.initOptions();

            if (_.isArray(window.apoData) !== true) {
                window.apoData = [];
                $.each(this.options.options, function (index, option) {
                    window.apoData[option.id] = [];
                });
            }

            this.toggleOptions();

            return this;
        },

        /**
         * Triggers one time after init price (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        afterInitPrice: function afterFirstRun(optionConfig, productConfig, base, self)
        {
            this.options.firstRunProcessed = [];
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
            var self = this;
            var optionField = $(option).closest('[option_id]');
            var optionId = optionField.attr('option_id');
            var optionObject = self.getOptionObject(optionId);

            var optionTypeField = $(option).find('[option_type_id]').first();
            if (optionTypeField.length < 1) {
                optionTypeField = $(option).closest('[option_type_id]');
            }

            if (optionTypeField) {
                var valueId = optionTypeField.attr('option_type_id'),
                    object = self.getOptionObject(valueId);
            } else {
                var object = optionObject;
            }

            if ($.inArray(optionObject.type, ['drop_down', 'multiple']) !== -1) {
                if (optionObject.type == 'drop_down') {
                    // For dropdown - for selected select options only
                    $('#' + option.attr('id') + ' option:selected').each(function () {
                        self.toggleDropdown(optionObject, self.getOptionObject($(this).attr('option_type_id')));
                    });
                } else {
                    // For multiselect - for all select options
                    var selectedMultiselectValues = $('#' + option.attr('id') + ' option:selected');
                    if (selectedMultiselectValues.length > 0) {
                        self.toggleMultiselect(optionObject, selectedMultiselectValues);
                    } else {
                        self.resetMultiselect(optionObject);
                    }
                }
            } else if ($.inArray(optionObject.type, ['checkbox', 'radio']) !== -1) {
                if (optionObject.type == 'radio') {
                    if ($(option).is(':checked')) {
                        self.toggleRadio(optionObject, object);
                    }
                } else {
                    if ($(option).is(':checked')) {
                        self.toggleCheckbox(optionObject, object);
                    } else {
                        self.resetCheckbox(optionObject, object);
                    }
                }
            }

            $.each(this.options.options, function (index, option) {
                option.toggle();
            });
        },

        toggleDropdown: function (option, changedValue) {
            var self = this;

            // For --Please Select-- - unselect all selected values
            if (typeof changedValue.id === "undefined" && _.isArray(window.apoData[option.id])) {
                $.each(window.apoData[option.id], function (index, value) {

                    var index = window.apoData[option.id].indexOf(value);
                    if (index !== -1) {
                        window.apoData[option.id].splice(index, 1);
                        self.toggleOption(self.getOptionObject(value));
                    }
                });
            }

            // For select "normal" value
            if (typeof changedValue.id !== "undefined") {
                // Toggle unselected values
                if (_.isArray(window.apoData[option.id])) {
                    $.each(window.apoData[option.id], function (index, value) {

                        var index = window.apoData[option.id].indexOf(value);
                        if (value.id !== changedValue.id && index !== -1) {
                            window.apoData[option.id].splice(index, 1);
                            self.toggleOption(self.getOptionObject(value));
                        }
                    });
                }

                // Toggle selected value
                window.apoData[option.id].push(changedValue.id);
                self.toggleOption(changedValue);
            }
        },

        toggleMultiselect: function (option, changedValues) {
            var self = this;

            var changedValueObjects = [];
            $.each(changedValues, function (index, changedValue) {
                changedValueObjects.push($(changedValue).attr('option_type_id'));
            });

            // For select "normal" value
            // Toggle unselected values
            $.each(window.apoData[option.id], function (index, value) {
                var currentIndex = changedValueObjects.indexOf(value);
                if (currentIndex === -1) {
                    index = window.apoData[option.id].indexOf(value);
                    window.apoData[option.id].splice(index, 1);
                    self.toggleOption(self.getOptionObject(value));
                }
            });

            $.each(changedValues, function (index, changedValue) {
                // Toggle selected value
                var changedValueObject = self.getOptionObject($(changedValue).attr('option_type_id'));
                var currentIndex = window.apoData[option.id].indexOf(changedValueObject.id);
                if (currentIndex === -1) {
                    window.apoData[option.id].push(changedValueObject.id);
                    self.toggleOption(changedValueObject);
                }
            });
        },

        resetMultiselect: function (option) {
            var self = this;

            // unselect all values, which already in apoData)
            $.each(window.apoData[option.id], function (index, value) {

                var currentIndex = window.apoData[option.id].indexOf(value);
                if (currentIndex !== -1) {
                    self.toggleOption(self.getOptionObject(value));
                }
            });
            window.apoData[option.id] = [];
        },

        toggleRadio: function (option, changedValue) {
            var self = this;

            // For select "normal" value
            if (typeof changedValue.id !== "undefined") {
                // Toggle unselected values
                if (_.isArray(window.apoData[option.id])) {
                    $.each(window.apoData[option.id], function (index, value) {

                        var index = window.apoData[option.id].indexOf(value);
                        if (value.id !== changedValue.id && index !== -1) {
                            window.apoData[option.id].splice(index, 1);
                            self.toggleOption(self.getOptionObject(value));
                        }
                    });
                }

                // Toggle selected value
                window.apoData[option.id].push(changedValue.id);
                self.toggleOption(changedValue);
            }
        },

        toggleCheckbox: function (option, changedValue) {
            var self = this;

            // For select "normal" value
            if (typeof changedValue.id !== "undefined") {
                // Toggle selected value
                window.apoData[option.id].push(changedValue.id);
                self.toggleOption(changedValue);
            }
        },

        resetCheckbox: function (option, changedValue) {
            var self = this;

            // Toggle unselected value
            var currentIndex = window.apoData[option.id].indexOf(changedValue.id);
            if (currentIndex !== -1) {
                window.apoData[option.id].splice(currentIndex, 1);
                self.toggleOption(self.getOptionObject(changedValue.id));
            }
        },

        toggleOptions: function () {
            var self = this;
            // toggle options: show or hide dependencies, deselect if hide
            $.each(this.options.options, function (index, option) {

                $.each(option.values, function (index, value) {
                    value.toggle();
                    self.options.firstRunProcessed.push(value.id);
                });

                option.toggle();
                self.options.firstRunProcessed.push(option.id);
            });

            return this;
        },

        toggleOption: function (object) {
            if (this.options.firstRunProcessed.indexOf(object.id) !== -1) {
                return;
            }

            var self = this;
            var childDependencies = _.isUndefined(object.type) ?
                self.options.valueChildren :
                self.options.optionChildren;

            if ($.inArray(object.id, _.keys(childDependencies)) === -1) {
                return this;
            }

            var children = childDependencies[object.id];

            $.each(children, function (index, childId) {
                var valueObj = self.getOptionObject(childId);
                if (valueObj) {
                    valueObj.toggle();
                    if (self.isNeedToSkipToggleOptionProcess(valueObj)) {
                        return;
                    }
                    var isChildSelected = window.apoData[valueObj.getOption().id].indexOf(valueObj.id);
                    if (isChildSelected !== -1) {
                        self.toggleOption(valueObj);
                    }
                }
            });

            return this;
        },

        getOptionObject: function (id) {

            var object = '';
            $.each(this.options.options, function (index, option) {
                if (option.id == id) {
                    object = option;
                    return false;
                }
                $.each(option.values, function (index, value) {
                    if (value.id == id) {
                        object = value;
                        return false;
                    }
                });
            });

            return object;
        },

        initOptions: function () {
            var self = this,
                isValid,
                getType,
                toggle,
                reset;

            /**
             * check if option or value is valid:
             * if true - show,
             * false - hide.
             *
             * @param object
             * @returns {boolean}
             */
            isValid = function (object) {
                // init parent dependencies:
                // if object is option - use optionParents,
                // else - valueParents.
                var parentDependencies = _.isUndefined(object.type) ? self.options.valueParents : self.options.optionParents;

                // 1. If object not exist in parentDependencies then it is not dependent
                // and return true.
                if ($.inArray(object.id, _.keys(parentDependencies)) === -1) {
                    return true;
                }

                // 2. If any of parents are selected - return true
                var parentSelected = false;
                var parents = parentDependencies[object.id]; // parent values ids

                $.each(parents, function (index, parentId) {
                    var field = $('[option_type_id="'+parentId+'"]');
                    var type = object._getType(parentId);

                    // checkbox and radio
                    if ($.inArray(type, ['checkbox', 'radio']) !== -1) {
                        var element = field.children('input');

                        if (element.is(':checked')) {
                            parentSelected = true;
                        }
                    }

                    // drop-down and multiselect
                    if ($.inArray(type, ['drop_down', 'multiple']) !== -1) {
                        var elements = field.parent('select').find(':selected');

                        $.each(elements, function (index, element) {
                            if ($(element).attr('option_type_id') == parentId) {
                                parentSelected = true;
                            }
                        });
                    }

                    if (parentSelected) {
                        return true;
                    }
                });

                return parentSelected;
            };

            /**
             * Retrieve option type, by value id.
             * Used for detect parent dependent option type.
             *
             * @param valueId
             * @returns {string}
             */
            getType = function (valueId) { // return option type by value
                var type = '';

                $.each(self.options.options, function (index, option) {
                    $.each(option.values, function (index, value) {
                        if (valueId == value.id) {
                            type = value.getOption().type;
                            return;
                        }
                    });
                    if (type) {
                        return;
                    }
                });

                return type;
            };

            toggle = function (object) {
                var isOption = _.isUndefined(object.type) ? false : true,
                    field = isOption ? $('[option_id="'+object.id+'"]') : $('[option_type_id="'+object.id+'"]'),
                    isRequired = false;

                if (isOption && typeof self.options.optionRequiredConfig != 'undefined') {
                    isRequired = typeof self.options.optionRequiredConfig[object.id] != 'undefined' ?
                    self.options.optionRequiredConfig[object.id] :
                    false;
                }
                // toggle visibility
                if (object.isValid()) {
                    field.show();
                    if (isOption && isRequired) {
                        if (field.hasClass('date')) {
                            self.enableDatetimeValidation(field);
                        } else {
                            field.addClass('required');
                            field.find('input, select, textarea, .field').addClass('required');
                        }
                    }
                } else {
                    field.hide();
                    if (isOption && isRequired) {
                        if (field.hasClass('date')) {
                            self.disableDatetimeValidation(field);
                        } else {
                            field.removeClass('required');
                            field.find('input, select, textarea, .field').removeClass('required');
                        }
                    }
                }

                // reset element
                object.reset();

                return object;
            },

            reset = function (value) {
                var isOption = _.isUndefined(value.type) ? false : true;
                if (isOption) {
                    return this;
                }

                var field = $('[option_type_id="'+value.id+'"]');
                if (field.css('display') != 'none') {
                    return this;
                }

                var type = value.getOption().type;
                var element = null;

                // checkbox and radio
                if ($.inArray(type, ['checkbox', 'radio']) !== -1) {
                    element = field.children('input');

                    element.removeAttr('checked');
                    element.trigger('change');
                }

                // drop-down and multiselect
                if ($.inArray(type, ['drop_down', 'multiple']) !== -1) {
                    element = field.parent('select');

                    field.removeAttr('selected');
                    element.trigger('change');
                }

                // update product price
                var priceOptions = $(self.options.addToCartSelector).data('magePriceOptions');
                if (!_.isUndefined(priceOptions) && !_.isNull(element)) {
                    priceOptions._onOptionChanged({target: element});
                }

                return this;
            },

            $('[option_id]').each(function (index, option) {

                var values = [];
                var optionObj = {}; // create emty option object to transfer the link to it to value

                $(option).find('[option_type_id]').each(function (index, value) {

                    var valueObj = {
                        id: $(value).attr('option_type_id'),
                        isValid: function () {
                            return isValid(this);
                        },
                        _getType: function (valueId) { // return option type by value
                            return getType(valueId);
                        },
                        toggle: function () {
                            return toggle(this);
                        },
                        reset: function () {
                            return reset(this);
                        },
                        getOption: function () {
                            return optionObj;
                        }
                    };

                    values.push(valueObj);
                });

                optionObj = {
                    id: $(option).attr('option_id'),
                    type: self.options.optionTypes[$(option).attr('option_id')],
                    values: values,
                    isValid: function () { // option is valid if it is not SELECT type or if any of values is valid
                        // 1. check if not SELECT option type
                        // If not SELECT - get parent values and validate
                        if (_.isEmpty(this.values)) {
                            return isValid(this);
                        }

                        // 2. If option is SELECT type - check if any of his values is valid
                        var valuesIsValid = false;
                        $.each(this.values, function (index, value) {
                            if (value.isValid()) {
                                valuesIsValid = true;
                                return;
                            }
                        });

                        return valuesIsValid;
                    },
                    _getType: function (valueId) { // return option type by value
                        return getType(valueId);
                    },
                    reset: function () {
                        return reset(this);
                    },
                    toggle: function () {
                        return toggle(this);
                    }
                };

                self.options.options.push(optionObj);
            });

            return this;
        },

        disableDatetimeValidation: function (field) {
            this.setDatetimeValidation(field, false);
        },

        enableDatetimeValidation: function (field) {
            this.setDatetimeValidation(field, true);
        },

        setDatetimeValidation: function (field, enable) {
            var fromKey = enable ? '_date_' : '_datetime_';
            var toKey = enable ? '_datetime_' : '_date_';
            var datetimeValidationField = field.find("input:hidden[name^='validate" + fromKey + "']");
            if (!_.isUndefined(datetimeValidationField) && datetimeValidationField.length > 0) {
                datetimeValidationField.attr(
                    'name',
                    datetimeValidationField.attr('name').replace(fromKey, toKey)
                );
            }
        },

        isNeedToSkipToggleOptionProcess: function (valueObj) {
            if (!_.isUndefined(valueObj.type)) {
                return true;
            }
            if (_.isUndefined(valueObj.getOption().id)
                || _.isUndefined(window.apoData[valueObj.getOption().id])
            ) {
                return true;
            }
            return false;
        }
    });

    return $.mageworx.optionDependency;
});