/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'Magento_Ui/js/form/components/fieldset',
        'uiRegistry'
    ],
    function (Fieldset, reg) {
        'use strict';
        return Fieldset.extend(
            {
                defaults: {
                    valuesForOptions: [],
                    imports: {
                        toggleVisibility: '${$.ns}.${$.ns}.settings.entity:value',
                        mapVisible: '${$.ns}.${$.ns}.source.check_button:showMap'
                    },
                    openOnShow: true,
                    isShown: false,
                    inverseVisibility: false
                },
                toggleVisibility: function (selected) {
                    this.isShown = (selected in this.valuesForOptions);
                    var bool = reg.get(this.ns + '.' + this.ns + '.source.check_button').showMap();
                    this.visible((this.isShown == true && bool == 1) ? true : false);
                    if (this.openOnShow) {
                        this.opened((this.isShown == true && bool == 1) ? true : false);
                    }
                },
                mapVisible: function (value) {
                    this.visible((this.isShown == true && value == 1) ? true : false);
                    if (this.openOnShow) {
                        this.opened((this.isShown == true && value == 1) ? true : false);
                    }
                }
            }
        );
    }
);