define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    var amrulesForm = {
        update: function (type) {
            var action = '';
            this.resetFields(type);

            var actionFieldset = $('#' + type +'rule_actions_fieldset_').parent();

            window.amRulesHide = 0;

            actionFieldset.show();
            if (typeof window.amPromoHide !="undefined" && window.amPromoHide == 1) {
                actionFieldset.hide();
            }

            var selector = $('[data-index="simple_action"] select');
            if (type !== 'sales_rule_form') {
                action = selector[1] ? selector[1].value : selector[0].value;
            } else {
                action = selector.val();
            }

            switch (action) {
                case 'thecheapest':
                case 'themostexpencive':
                case 'moneyamount':
                case 'aftern_fixed':
                case 'aftern_disc':
                case 'aftern_fixdisc':
                case 'eachn_perc':
                case 'eachn_fixdisc':
                case 'eachn_fixprice':
                case 'groupn':
                case 'groupn_disc':
                    this.showFields(['amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'eachmaftn_perc':
                case 'eachmaftn_fixdisc':
                case 'eachmaftn_fixprice':
                    this.showFields(['amrulesrule[eachm]', 'amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'buyxgety_perc':
                case 'buyxgety_fixprice':
                case 'buyxgety_fixdisc':
                    this.showFields(['amrulesrule[promo_skus]', 'amrulesrule[promo_cats]', 'amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'buyxgetn_perc':
                case 'buyxgetn_fixprice':
                case 'buyxgetn_fixdisc':
                    this.showFields(['amrulesrule[promo_skus]', 'amrulesrule[nqty]', 'amrulesrule[promo_cats]', 'amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'setof_percent':
                case 'setof_fixed':
                    actionFieldset.hide();
                    window.amRulesHide = 1;
                    this.showFields(['amrulesrule[promo_skus]', 'amrulesrule[promo_skus]', 'amrulesrule[promo_cats]', 'amrulesrule[max_discount]'], type);

                    //this.hideFields(['discount_step']);
                    break;
            }



        },

        resetFields: function (type) {
            this.showFields([
                'discount_qty', 'discount_step', 'apply_to_shipping', 'simple_free_shipping'
            ], type);
            this.hideFields([
                'amrulesrule[skip_rule]',
                'amrulesrule[max_discount]',
                'amrulesrule[nqty]',
                'amrulesrule[promo_skus]',
                'amrulesrule[promo_cats]',
                'amrulesrule[priceselector]',
                'amrulesrule[eachm]'
            ], type);
        },

        hideFields: function (names, type) {
            return this.toggleFields('hide', names, type);
        },

        showFields: function (names, type) {
            return this.toggleFields('show', names, type);
        },

        addPrefix: function (names, type) {
            for (var i = 0; i < names.length; i++) {
                names[i] = type + '.' + type + '.' + 'actions.' + names[i];
            }

            return names;
        },

        toggleFields: function (method, names, type) {
            registry.get(this.addPrefix(names, type), function () {
                for (var i = 0; i < arguments.length; i++) {
                    arguments[i][method]();
                }
            });
        }

    };

    return amrulesForm;
});