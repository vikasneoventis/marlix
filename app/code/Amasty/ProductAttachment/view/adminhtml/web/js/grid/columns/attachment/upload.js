/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_ProductAttachment
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'ko',
    'Amasty_ProductAttachment/js/init-drop-zone',
    'Amasty_ProductAttachment/js/byte-convert',
    'jquery/file-uploader',
], function (Column, $, alert, ko, initDropZone, byteConvert) {
    'use strict';

    var column = Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_ProductAttachment/grid/cells/attachment/upload',
            fieldClass: {
                'data-grid-upload-cell': true
            }
        },

        getFieldHandler: function (row) {},

        getFileId: function(row) {
            return 'amfile-'+this.getProductId(row);
        },

        getProductId: function(row) {
            return row['entity_id'];
        },

        getFormKey: function(row) {
            return row[this.index + '_form_key'];
        },

        getStoreId: function(row) {
            return row[this.index + '_store_id'];
        },

        getUploadUrl: function(row) {
            return row[this.index + '_upload_url'];
        },

        getMaxFileSize: function(row) {
            return row[this.index + '_upload_max_size'];
        },

        getDragAndDropText: function() {
            return $.mage.__('Click or drop files here');
        },

        getFileType: function() {
            return 'attachment';
        }

    });

    ko.bindingHandlers.fileUpload = {
        init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            $(element).fileupload({
                dataType: 'json',
                sequentialUploads: true,
                maxFileSize: 200,
                dropZone: $(element).siblings('.drop-zone'),
                add: function (e, data) {
                    $(this).fileupload('process', data).done(function () {
                        var fileSize, maxFileSize;
                        var element = e.target;
                        var errorMessage = null;

                        $.each(data.files, function (index, file) {
                            fileSize = file.size;
                            if (typeof fileSize == "undefined") {
                                errorMessage = 'We could not detect a size.';
                            }

                            maxFileSize = $(element).attr('maxFileSize');
                            if (fileSize >= maxFileSize) {
                                errorMessage = 'Can not upload File ' + file.name + '. Max allowed size is ' + byteConvert(maxFileSize) + '.';
                            }
                        });
                        if (errorMessage === null) {
                            data.submit();
                        } else {
                            alert({content: errorMessage});
                        }
                    });
                },
                progressall: function(event, data) {
                    var element = event.target;
                    this.spinner = $(element).closest('.upload-attachment').find('.spinner');
                    this.spinner.show();
                },
                done: function (event, data) {
                    this.spinner.hide();
                    if (data.result && (data.result.hasOwnProperty('errorcode') || data.result.hasOwnProperty('error'))) {
                        var alertMessage = data.result.hasOwnProperty('message') ? data.result.message : data.result.error;
                        alert({content: alertMessage});
                    } else {
                        var element = event.target;

                        var attachment = $('<span/>');
                        attachment.append($('<a/>').attr('href', data.result.url).text(data.result.label));

                        attachment.clone().appendTo($('#amfile-attachment-list-' + data.result.product_id));
                        $('<br/>').appendTo($('#amfile-attachment-list-' + data.result.product_id));

                        var isOk = $("<div/>").addClass('success-upload-image');
                        $(element).closest('.upload-attachment').append(isOk);
                        isOk.fadeOut(1200);
                    }

                },
                fail: function (e, data) {
                    this.spinner.hide();
                    alert({content: data.result.error});
                }
            });
        },
        update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            $(document).bind('dragover', function (e) {
                e.preventDefault();
                initDropZone(e);
            });
        }
    };
    return column;
});