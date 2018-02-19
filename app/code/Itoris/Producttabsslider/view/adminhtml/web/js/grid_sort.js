/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
var gridReload;
requirejs([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/loader',
    'mage/backend/validation'
], function ($, productGallery) {
    'use strict';
    $(function(){
        window.gridProdTabsSort={
            init:function() {
            var $=this.jq_;
            $('#itoris_grid_tabs').on('click', '.sort-arrow-down', function (event) {
                event.preventDefault();
                console.log(sortUrl);
                var down = $(this).attr('data_order_down');
                var this_sort = $(this).attr('data_order_this');
                var id_this = $(this).attr('data_id_tab_this');
                var id_down = $(this).attr('data_id_tab_down');
                if (!$('.sort-arrow-down').attr('data_product') && !$('.sort-arrow-down').attr('data_store')) {
                    // itoris_grid_tabsJsObject.reload();
                    $.ajax({
                        url: sortUrl,
                        data: {down: down, this_sort: this_sort, id_this: id_this, id_down: id_down},
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });
                } else if (!$('.sort-arrow-down').attr('data_product') && $('.sort-arrow-down').attr('data_store')) {
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_next_value');
                    var id_this = $(this).attr('data_id_tab_this');
                    var id_down = $(this).attr('data_id_tab_down');
                    var store = $(this).attr('data_store');
                    $.ajax({
                        url: sortUrl,
                        data: {
                                down: down,
                                this_sort: this_sort,
                                id_this: id_this,
                                id_down: id_down,
                                thisValue: id_value,
                                nextValue: next_value,
                                id_store:store
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });
                }
                else if ($('.sort-arrow-down').attr('data_product') && !$('.sort-arrow-down').attr('data_store')) {
                    var product = $(this).attr('data_product');
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_next_value');
                    $.ajax({
                        url: sortUrl,
                        data: {
                            down: down,
                            this_sort: this_sort,
                            id_this: id_this,
                            id_down: id_down,
                            id_product: product,
                            thisValue: id_value,
                            nextValue: next_value
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });
                }
                else {
                    var product = $(this).attr('data_product');
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_next_value');
                    var store = $(this).attr('data_store');
                    $.ajax({
                        url: sortUrl,
                        data: {
                            down: down,
                            this_sort: this_sort,
                            id_this: id_this,
                            id_down: id_down,
                            id_product: product,
                            id_store: store,
                            thisValue: id_value,
                            nextValue: next_value
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });
                }
            });
            $('#itoris_grid_tabs').on('click', '.sort-arrow-up', function (event) {
                event.preventDefault();
                var up = $(this).attr('data_order_up');
                var this_sort = $(this).attr('data_order_this');
                var id_this = $(this).attr('data_id_tab_this');
                var id_up = $(this).attr('data_id_tab_up');
                // itoris_grid_tabsJsObject.reload();
                if (!$('.sort-arrow-up').attr('data_product') && !$('.sort-arrow-up').attr('data_store')) {
                    $.ajax({
                        url: sortUrl,
                        data: {up: up, this_sort: this_sort, id_this: id_this, id_up: id_up},
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });

                } else if (!$('.sort-arrow-down').attr('data_product') && $('.sort-arrow-down').attr('data_store')) {
                    var up = $(this).attr('data_order_up');
                    var store = $(this).attr('data_store');
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_prev_value');
                    var this_sort = $(this).attr('data_order_this');
                    var id_this = $(this).attr('data_id_tab_this');
                    var id_up = $(this).attr('data_id_tab_up');
                    $.ajax({
                        url: sortUrl,
                        data: {
                            up: up,
                            this_sort: this_sort,
                            id_this: id_this,
                            id_up: id_up,
                            id_store: store,
                            thisValue: id_value,
                            prevValue: next_value
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });

                }
                else if ($('.sort-arrow-up').attr('data_product') && !$('.sort-arrow-up').attr('data_store')) {
                    var product = $(this).attr('data_product');
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_prev_value');
                    $.ajax({
                        url: sortUrl,
                        data: {
                            up: up,
                            this_sort: this_sort,
                            id_this: id_this,
                            id_up: id_up,
                            id_product: product,
                            thisValue: id_value,
                            prevValue: next_value
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });

                } else {
                    var store = $(this).attr('data_store');
                    var product = $(this).attr('data_product');
                    var id_value = $(this).attr('data_id_value');
                    var next_value = $(this).attr('data_prev_value');
                    $.ajax({
                        url: sortUrl,
                        data: {
                            up: up,
                            this_sort: this_sort,
                            id_this: id_this,
                            id_up: id_up,
                            id_product: product,
                            id_store: store,
                            thisValue: id_value,
                            prevValue: next_value
                        },
                        type: "POST",
                        dataType: 'json',
                        showLoader: true,
                        complete: function () {
                            itoris_grid_tabsJsObject.reload();
                        }
                    });

                }
            });
        },
            jq_:jQuery
        }

        window.gridProdTabsSort.init();
    });



});


