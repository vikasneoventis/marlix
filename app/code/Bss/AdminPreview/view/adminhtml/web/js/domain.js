define([
    'jquery',
    'jquery/ui',
    "domReady!"
], function ($) {
    'use strict';
    $.widget('mage.AdminPreview', {
        options: {
            list_domain:{},
        },
        _create: function () {
            var $widget = this;
            var list_domain = $widget.options.list_domain;
            if ($(list_domain).length) {
                $(document).ready(function(){
                    $('li.item-domain ul').find('li').remove();
                    for (var i = 0; i < $(list_domain).length; i++) {
                        $('li.item-domain ul').append('<li data-ui-id="menu-bss-adminpreview-website" class="item-website level-2" role="menu-item"><a href="' + list_domain['url'] + '" target="_blank"><span>' + list_domain['name'] + '</span></a></li>')
                    }
                })
            }else{
                $('li.item-domain').remove();
            }
        }
    });
    return $.mage.AdminPreview;
});
