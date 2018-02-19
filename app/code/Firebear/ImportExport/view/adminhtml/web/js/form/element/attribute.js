/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'jquery',
        'underscore',
        'Firebear_ImportExport/js/form/element/additional-select',
        'uiRegistry',
        'mage/translate'
    ],
    function ($, _, Acstract, reg, $t) {
        'use strict';

        return Acstract.extend(
            {
                defaults: {
                    sourceOptions: null,
                    listens: {
                        'value': 'onSelectValueChange'
                    }
                },
                initialize: function () {
                    this._super();
                    var self = this;
                    var options = $.parseJSON(localStorage.getItem('columns'));
                    var newOptions = [];
                    newOptions.push({label: $t('Select A Column'), value: ''});
                    _.each(
                        options,
                        function (value) {
                            newOptions.push({label: value, value: value});
                        }
                    );

                    reg.get(
                        this.parentName,
                        function (object) {
                            var index = object.index;
                            var data = reg.get(self.provider).data;
                            var system = object.data().source_data_system;
                            var record = reg.get(object.parentName);
                            
                            if (_.size(record.prevData)) {
                                _.each(
                                    record.prevData,
                                    function (element) {
                                        if (system == element.source_data_system) {
                                            self.value(element.source_data_import);
                                        }
                                    }
                                )
                            } else if("special_map" in data && _.size(data.special_map) > 0) {
                                _.each(
                                    data.special_map.source_data_map,
                                    function (element) {
                                        if (system == element.source_data_system) {
                                            self.value(element.source_data_import);
                                        }
                                    }
                                )
                            }
                        }
                    );


                    this.setOptions(newOptions);
                    return this;
                },
                initConfig: function (config) {
                    this._super();
                    this.sourceOptions = config.options;

                    return this;
                },
                onSelectValueChange: function (value) {
                    if (value != 'undefined') {
                        this.value(value);
                    }
                    var include = false;
                    _.each(this.sourceOptions, function (index) {
                        if (index == value) {
                            include = true;
                        }
                    });
                    reg.get(this.parentName + '.source_data_replace', function (object) {
                        object.disabled(include);
                    })
                },
            }
        )
    }
);
