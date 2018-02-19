/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore'
], function ($, Abstract, registry, _) {
    'use strict';

    return Abstract.extend({

        defaults: {
            target: {
                option: '${ $.parentName }.option_id',
                value: '${ $.parentName }.value_id',
            },
        },

        initialize: function () {
            this._super();

            this.loadOptionsList();

            return this;
        },

        /**
         * On option change handler.
         * Update values for selected option.
         *
         * @param {String} value
         */
        onUpdate: function (currentOptionId) {
            this.loadValuesList(currentOptionId);

            return this._super();
        },

        /**
         * Load all available options.
         */
        loadOptionsList: function () {
            var list = [],
                provider = registry.get(this.provider),
                data = provider.data,
                options = _.isUndefined(data.mageworx_optiontemplates_group) ? data.product.options : data.mageworx_optiontemplates_group.options,
                dynamicRow = registry.get('index = ' + this.dependencyModalIndex),
                titleId = '';

            for (var i = 0; i < options.length; i++) {
                var option = options[i],
                    optionTitle = option['title'],
                    optionId = this._getId(option, 'option');

                if (this._canAddOptionToList(option, dynamicRow.currentOption)) {
                    if (!_.isEmpty(option.option_title_id) && this.isTitleIdEnabled) {
                        titleId =  ' - [' + option.option_title_id + ']';
                    }
                    list.push({
                        'label': optionTitle + titleId,
                        'value': optionId,
                    });
                }
            }

            this.initialOptions = list;
            this.options(list);
        },

        /**
         * We add available options only,
         * which has 'select' type.
         * We do not add current option.
         *
         * @param option
         * @param currentOption
         * @returns {boolean}
         * @private
         */
        _canAddOptionToList: function (option, currentOption) {
            var optionId = this._getId(option, 'option'),
                currentOptionId = this._getId(currentOption, 'option'),
                optionType = option['type'],
                optionIsDelete = option['is_delete'];

            if (this.isSelect(optionType) && optionId != currentOptionId && !optionIsDelete) {
                return true;
            }

            return false;
        },

        /**
         * Load all available values for selected option.
         *
         * @param {integer} currentOptionId
         */
        loadValuesList: function (currentOptionId) {
            var provider = registry.get(this.provider),
                data = provider.data,
                options = _.isUndefined(data.mageworx_optiontemplates_group) ? data.product.options : data.mageworx_optiontemplates_group.options;

            for (var i = 0; i < options.length; i++) {
                var optionId = this._getId(options[i], 'option');

                if (optionId == currentOptionId) {
                    var selectedOption = options[i];
                }
            }

            var selectedOptionValues = this.getSelectedOptionValues(selectedOption),
                values = registry.get(this.target.value);

            values.initialOptions = selectedOptionValues;
            values.options(selectedOptionValues);
        },

        getSelectedOptionValues: function (selectedOption) {
            var selectedOptionValues = selectedOption.values,
                result = [],
                object = this,
                titleId = '';

            selectedOptionValues.forEach(function (option) {
                var id = object._getId(option, 'value');
                var isDelete = option['is_delete'];

                if (!isDelete) {
                    if (!_.isEmpty(option.option_type_title_id) && object.isTitleIdEnabled) {
                        titleId =  ' - [' + option.option_type_title_id + ']';
                    }
                    result.push({
                        'label': option.title + titleId,
                        'value': id,
                    });
                }
            });

            return result;
        },

        /**
         * Check if option is 'select' type.
         *
         * @param {String} value
         * @returns {Boolean}
         */
        isSelect: function (optionType) {
            if ($.inArray(optionType, ['drop_down', 'radio', 'checkbox', 'multiple', 'swatch']) !== -1) {
                return true;
            }

            return false;
        },

        /**
         * Retrieve option|value id.
         *
         * @param {Object} object - option or value object
         * @param {String} type - type of object (option or value)
         * @returns {integer}
         */
        _getId: function (object, type) {
            var isSchedule = registry.get('index = catalogstaging_update_form');

            if (type == 'option') {
                if (isSchedule) {
                    return object['record_id'];
                } else {
                    return object['mageworx_option_id'] || object['record_id'];
                }
            }

            if (type == 'value') {
                if (isSchedule) {
                    return object['record_id'];
                } else {
                    return object['mageworx_option_type_id'] || object['record_id'];
                }
            }
        },
    });
});