/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
var $_=jQuery

var tabModal;
var elmentEdit;
var confirmbool;
requirejs([
    'jquery',
    'jquery/ui',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'mage/loader',
    'mage/backend/validation'
], function ($, productGallery,$t) {
    'use strict';
    tabModal=$('#tab_form_add_popup_product').modal({
        type: 'slide',
        buttons: [],
    });
    $_(function(){

        window.initProdTabs={
            init:function(){
                var $= this.jq_;
                $('#itoris_grid_tabs').on('click', '#add_tab', function () {
                    //$('[data-rale="pannel"]').trigger('show.loader');
                    // itoris_grid_tabsJsObject;
                    $_.ajax({
                        url: addnewTabUrl,
                        type: "POST",
                        showLoader: true,
                        dataType: 'html',
                        complete:function(){


                        },
                        success: function (data) {

                            tabModal.html(data);
                            $('.form-inline .itoris_producttabs_form').append('<button class="add_tab_form btn pull-right" title="Add tab" form="edit_form"  type="button" class="btn btn-primary add tabs action-default scalable save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">' + $t('Add Tab') + '</button>');
                            tabModal.modal('openModal');
                            $('.add_tab_form').click(function (event) {
                                event.preventDefault();
                                if ($('span').is('#itoris_global_tabs_content_parent'))
                                    $('.action-show-hide').click();
                                if ($(' .itoris_producttabs_form').valid() == true) {
                                    $_.ajax({
                                        url: newTabsSave,
                                        data: $('.itoris_producttabs_form').serializeArray(),
                                        type: "POST",
                                        dataType: 'json',
                                        showLoader: true,
                                        success: function (data) {

                                            itoris_grid_tabsJsObject.reload();
                                            tabModal.modal('closeModal');


                                        }
                                    });

                                } else {
                                    $('.itoris_producttabs_form').submit();
                                }
                            });

                        }
                    });
                });

                $('#itoris_grid_tabs').on('click', '.edit_tabs_grid', function (event) {
                    event.preventDefault();
                    var id = $(this).attr('data_tab_custom');
                    $_.ajax({
                        url: urlEdit + 'id/' + id,
                        dataType: 'html',
                        type: "POST",
                        showLoader: true,
                        complete:function(){

                        },
                        success: function (data) {

                            tabModal.html(data);
                            $('.form-inline .itoris_producttabs_form').append('<button class="itoris_edit_tab" title="Add tab" form="edit_form"  type="button" class="btn btn-primary add tabs">' + $t('Save Tab') + '</button>');
                            tabModal.modal('openModal');
                            if (!$('span').is('#itoris_global_tabs_content_parent'))
                                $('.action-show-hide').click();
                            $('.itoris_edit_tab').click(function () {
                                if ($('span').is('#itoris_global_tabs_content_parent'))
                                    $('.action-show-hide').click();
                                if ($('.itoris_producttabs_form').valid() == true) {
                                    $_.ajax({
                                        url: newTabsSave,
                                        data: $('.itoris_producttabs_form').serializeArray(),
                                        type: "POST",
                                        dataType: 'json',
                                        showLoader: true,
                                        success: function (data) {

                                            itoris_grid_tabsJsObject.reload();
                                            tabModal.modal('closeModal');
                                        }
                                    });

                                } else {
                                    $('.itoris_producttabs_form').submit();
                                }
                            });

                        }
                    });
                    return false;

                });
                $('#itoris_grid_tabs').on('click', '.delete_tabs_grid', function (event) {
                    event.preventDefault();
                    confirmbool = confirm($t("Are you sure want to remove the tab(s)?"));
                    var id = $(this).attr('data_tab_custom');
                    if (confirmbool) {
                        $.ajax({
                            url: deleteTab + 'id/' + id,
                            type: "GET",
                            dataType: 'json',
                            showLoader: true,
                            success: function (data) {
                                itoris_grid_tabsJsObject.reload();
                            }
                        });
                    }

                })
            },
            jq_:jQuery
        }

        window.initProdTabs.init();
        /**/
    });
});


