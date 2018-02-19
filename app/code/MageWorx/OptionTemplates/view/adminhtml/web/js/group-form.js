/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'uiRegistry',
    'underscore'
], function (jQuery, Form, uiRegistry, _) {
    'use strict';

    return Form.extend({

        /**
         * Validate and save form.
         *
         * @param {String} redirect
         * @param {Object} data
         */
        save: function (redirect, data) {
            this.validate();

            data = this.addSelectedProductsData(data);

            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            }
        },

        /**
         *
         * @param data
         * @returns {*}
         */
        addSelectedProductsData: function (data) {

            var listing = uiRegistry.get('name = mageworx_optiontemplates_product_listing.mageworx_optiontemplates_product_listing.product_columns.ids');

            if (_.isUndefined(listing)) {
                return data;
            }

            var selections = listing.getSelections();
            this.source.data.mageworx_optiontemplates_group.products = selections.selected;

            return data;
        }

    });
});
