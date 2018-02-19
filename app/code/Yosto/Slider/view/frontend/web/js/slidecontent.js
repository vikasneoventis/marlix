/**
 * Created by nghiata on 12/28/2016.
 */
define(['jquery','ko'], function($, ko) {

    return function(config, element) {

        var viewModelConstructor = function() {
            this.content = config.config.content;
        };

        var viewModel = new viewModelConstructor();
        ko.applyBindings(viewModel);
    }

});