categoryDuplicate = function (url) {
    location.href = url;
}

confirmCategoryDuplicate = function () {
    require([
        'jquery',
        'jquery/ui',
        'Magento_Ui/js/modal/modal'
    ], function ($) {
        $('<div />').html('Duplicating a large number (100+) of subcategories might take up to 30 minutes. Please confirm if you\'d like to proceed now.')
            .modal({
                title: 'Confirm the duplication?',
                autoOpen: true,
                closed: function () {
                    // on close
                },
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        "class": "action-secondary",
                        click: function() {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Duplicate'),
                        "class": "action-primary",
                        click: processCategoryDuplicate
                    }
                ],
            });
    });
}

processCategoryDuplicate = function () {
    $('amcatcopy_category_duplicate_form').submit();
}
