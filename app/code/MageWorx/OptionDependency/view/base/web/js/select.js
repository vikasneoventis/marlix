define([
    'jquery',
    'underscore',
    'uiComponent'
], function ($, _, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            optionsSelector: 'select option[option_type_id]'
        },

        initialize: function () {
            var self = this;
            this._super();

            self.observeOptions();

            $('document').ready(function () {
                self.toggleAll();
            });
        },

        observeOptions: function () {
           var self = this;
           var target = $(self.optionsSelector);
           var observer = new MutationObserver(function (mutations) {
               mutations.forEach(function (mutationRecord) {
                   self.toggleSelectOptions($(mutationRecord.target));
               });
           });

           $.each(target, function (index, element) {
               observer.observe(element, { attributes : true, attributeFilter : ['style'] });
           });
        },

        toggleAll: function () {
            var self = this;

            $.each($(self.optionsSelector), function (index, element) {
                self.toggleSelectOptions($(element));
            });
        },

        toggleSelectOptions: function (value) {
            var valueTag = this._tagName(value);
            var parent = $(value.parent().get(0));
            var parentTag = this._tagName(parent);

            if (valueTag != 'option') {
                return;
            }

            if (value.css('display') != 'none') {
                if (parentTag != 'span') {
                    return;
                }
                value.unwrap('<span>');
            } else {
                if (parentTag == 'span') {
                    return;
                }
                value.wrap('<span>').hide();
            }
        },

        _tagName: function (el) {
            return $(el).prop("tagName").toLowerCase();
        },
    });
});