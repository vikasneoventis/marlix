/**  * Copyright © 2016 Magento. All rights reserved.  * See COPYING.txt for license details.  */
define([
    'jquery',
    'ko',
    'underscore',
    'uiRegistry',
    'uiLayout',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'MageWorx_OptionBase/versionResolver'

], function ($, ko, _, registry, layout, dynamicRows, versionResolver) {
    'use strict';

    var extendedDynamicRows = dynamicRows.extend({
        defaults: {
            currentValue: [],
            currentOption: [],
            provider: "",
            scope: "",
            pageSize: 100
        },

        initObservable: function () {
            this._super().observe(true, 'label');

            return this;
        },

        /**
         * Save current option data.
         *
         * @param {Object} params
         */
        setOptionData: function (params) {
            var provider = registry.get(params.provider),
                currentValue = provider.get(params.parentScope),
                currentOption = provider.get(this.getOptionScope(params.parentScope));

            this.provider = params.provider;
            this.scope = params.parentScope;
            this.currentValue = currentValue;
            this.currentOption = currentOption;
        },

        /**
         * Set title for Dynamic Row.
         *
         * @returns {exports}
         */
        setTitle: function () {
            var valueTitle = this.currentValue.title || '',
                optionTitle = this.currentOption.title || '',
                title = (valueTitle == optionTitle) ? optionTitle : optionTitle + ' - ' + valueTitle,
                label = this.defaultLabel + ': ' + title;

            this.set('label', label);

            return this;
        },

        /**
         * Retrieve current option scope based on current option value scope.
         *
         * @param {String} valueScope - current option value scope
         * @returns {String}
         */
        getOptionScope: function (valueScope) {
            return valueScope.split('.values')[0];
        },

        /**
         * Load saved dependencies into dynamic row grid in modal window.
         *
         * @returns {exports}
         */
        loadDependency: function () {
            var object = this;
            var dependencyContainer = this.currentValue[this.dependencyContainer];
            var savedDependencies = dependencyContainer ? JSON.parse(dependencyContainer) : [];

            savedDependencies = this.checkAvailability(savedDependencies);

            if (!savedDependencies.length) {
                return this;
            }

            $('body').trigger('processStart');
            var spinnerCounter = 0;
            for (var i = 0; i < savedDependencies.length; i++) {
                object.addChild(false, i, false); // add row with default data

                var recordScope = object._elems.last(); // get object of currently added row

                // wait when currently added row was added and fill it with saved data
                $.when(registry.promise(recordScope)).then(function (record) {
                    var intervalLoopCounter = 0;
                    var fillRowInterval = setInterval(function () {
                        intervalLoopCounter += 1;
                        if (!_.isUndefined(record)
                            && _.isFunction(record.getChild)
                            && !_.isUndefined(record.getChild('option_id'))
                            && !_.isUndefined(record.getChild('value_id'))
                        ) {
                            spinnerCounter += 1;
                            clearInterval(fillRowInterval);
                            object._fillRow(record, 'option_id', savedDependencies[record.index][0]);
                            object._fillRow(record, 'value_id', savedDependencies[record.index][1]);
                        } else if (intervalLoopCounter == 10) {
                            clearInterval(fillRowInterval);
                            alert($t('MageWorx OptionDependency load error. Please refresh the page'));
                            $('body').trigger('processStop');
                        }
                        if (spinnerCounter == savedDependencies.length) {
                            $('body').trigger('processStop');
                        }
                    }, 50);
                });
            }

            return this;
        },

        /**
         * To fill with data Record.
         *
         * @param {Object} record - object of dynamic row Record
         * @param {String} fieldName - name of field for pasting data to
         * @param {integer} value - option|value id
         * @returns {exports}
         */
        _fillRow: function (record, fieldName, value) {
            if (record) {
                record.getChild(fieldName).value(value);
            }

            return this;
        },

        /**
         * Before load saved dependencies
         * we check each option|value (from saved dependencies array) for existence
         * and modify default saved dependencies array.
         *
         * @param {Array} savedDependencies
         * @returns {Array} Only available options|values
         */
        checkAvailability: function (savedDependencies) {
            var optionIds = this._getAllOptionIds(),
                valueIds = this._getAllOptionValueIds(),
                result = [];

            for (var i = 0; i < savedDependencies.length; i++) {
                var optionId = savedDependencies[i][0],
                    valueId  = savedDependencies[i][1];

                if ($.inArray(optionId, optionIds) !== -1 && $.inArray(valueId, valueIds) !== -1) {
                    result.push(savedDependencies[i]);
                }
            }

            return result;
        },

        _getAllOptionIds: function () {
            var ids = [];
            var provider = registry.get(this.provider),
                data = provider.data,
                options = _.isUndefined(data.mageworx_optiontemplates_group) ? data.product.options : data.mageworx_optiontemplates_group.options;

            for (var i = 0; i < options.length; i++) {
                var option = options[i],
                    isDelete = option['is_delete'];

                if (registry.get('index = catalogstaging_update_form')) {
                    var opId = option['record_id'];
                } else {
                    var opId = option['mageworx_option_id'] || option['record_id'];
                }

                if (isDelete) {
                    continue;
                }

                ids.push(opId);
            }

            return ids;
        },

        _getAllOptionValueIds: function () {
            var ids = [];
            var provider = registry.get(this.provider),
                data = provider.data,
                options = _.isUndefined(data.mageworx_optiontemplates_group) ? data.product.options : data.mageworx_optiontemplates_group.options;

            for (var i = 0; i < options.length; i++) {
                var option = options[i],
                    isOptionDelete = option['is_delete'],
                    values = option['values'] || [];

                if (isOptionDelete) {
                    continue;
                }

                for (var j = 0; j < values.length; j++) {
                    var value = values[j],
                        isValueDelete = value['is_delete'];

                    if (registry.get('index = catalogstaging_update_form')) {
                        var valueId = value['record_id'];
                    } else {
                        var valueId = value['mageworx_option_type_id'] || value['record_id'];
                    }


                    if (isValueDelete) {
                        continue;
                    }

                    ids.push(valueId);
                }
            }

            return ids;
        },

        saveDependency: function () {
            var dependency = [];
            var records = this.elems();

            for (var i=0; i<records.length; i++) {
                var record = records[i];

                dependency.push([record.data().option_id, record.data().value_id]);
            }

            dependency = dependency.length ? JSON.stringify(dependency) : "";

            registry.get(this.provider).set(this.scope + '.' + this.dependencyContainer, dependency);

            return this;
        }
    });

    if (versionResolver.isSince22x() != -1) {
        return extendedDynamicRows.extend({
            deleteRecord: function (index, recordId) {
                var recordInstance,
                    lastRecord,
                    recordsData;

                if (this.deleteProperty) {
                    recordsData = this.recordData();
                    recordInstance = _.find(this.elems(), function (elem) {
                        return elem.index === index;
                    });
                    recordInstance.destroy();
                    this.elems([]);
                    this._updateCollection();
                    this.removeMaxPosition();
                    recordsData[recordInstance.index][this.deleteProperty] = this.deleteValue;
                    this.recordData(recordsData);
                } else {
                    this.update = true;

                    if (~~this.currentPage() === this.pages()) {
                        lastRecord =
                        _.findWhere(this.elems(), {
                            index: this.startIndex + this.getChildItems().length - 1
                        }) ||
                        _.findWhere(this.elems(), {
                            index: (this.startIndex + this.getChildItems().length - 1).toString()
                        });

                        lastRecord.destroy();
                    }

                    this.removeMaxPosition();
                    recordsData = this._getDataByProp(recordId);
                    this._updateData(recordsData);
                    this.update = false;
                }

                this._reducePages();
                this._sort();
            }
        });
    } else {
        return extendedDynamicRows;
    }
}); 