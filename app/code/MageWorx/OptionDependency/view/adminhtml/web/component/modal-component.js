/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'underscore',
    'Magento_Ui/js/modal/modal-component',
], function (registry, _, ModalComponent) {
    'use strict';

    return ModalComponent.extend({
        defaults: {
            dynamicRow: null,
        },

        /**
         * Open modal
         */
        openModal: function () {
            this._super();
            // set dynamicRow object when open modal
            this.dynamicRow = registry.get('index = ' + this.indexies.dependencies_dynamic_row);

            this.dynamicRow.setTitle();
            this.dynamicRow.loadDependency();
        },

        /**
         * Close modal
         */
        closeModal: function () {
            this._super();

            this.clearDynamicRow();

            // clear dynamicRow object when close modal
            this.dynamicRow = null;
        },

        /**
         * Save dependencies and close modal
         */
        saveDependencyData: function () {
            this.saveDependency();
            this.closeModal();
        },

        /**
         * Clear dynamic row before close modal window.
         *
         * @param {Object} dynamicRow - current Dependencies Dynamic Row
         */
        clearDynamicRow: function () {
            this.dynamicRow.clear();
            this.dynamicRow.recordData([]);
            this.dynamicRow.showSpinner(false);
        },

        /**
         * Save dependencies before clear dynamic row.
         *
         * @param {Object} dynamicRow - current Dependencies Dynamic Row
         */
        saveDependency: function () {
            this.dynamicRow.saveDependency();
        }
    });
});
