/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_ProductAttachment
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Amasty_ProductAttachment/template/grid/cells/attachment/preview.html',
    'Magento_Ui/js/modal/alert'
], function (Column, $, mageTemplate, attachmentPreviewTemplate, alert) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_ProductAttachment/grid/cells/attachment',
            fieldClass: {
                'data-grid-attachment-cell': true
            }
        },
        getAlt: function (row) {
            return row[this.index + '_alt']
        },
        getAttachmentList: function (row) {
            return row[this.index + '_list'];
        },

        getProductId: function(row) {
            return row['entity_id'];
        },

        getAttachmentListId: function(row) {
            return 'amfile-attachment-list-' + this.getProductId(row);
        },

        isPreviewAvailable: function() {
            return this.has_preview || false;
        },
        preview: function (row) {

            var attachmentList = this.getAttachmentList(row);
            var productId = this.getProductId(row);

            var modalHtml = mageTemplate(attachmentPreviewTemplate,
                {
                    attachmentList: attachmentList,
                    productId : productId
                }
            );

            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                buttons: [],
             }
            ).trigger('openModal');
        },
        getFieldHandler: function (row) {
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        },

        getMaxAttachmentItems: function() {
            return 3;
        }

    });
});