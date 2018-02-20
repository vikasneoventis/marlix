/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'Magento_Checkout/js/view/shipping-information'
], function (
    Component
) {
    'use strict';

    return Component.extend({
        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return false;
        }
    });
});
