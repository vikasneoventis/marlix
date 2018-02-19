/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

define(
    ['jquery', 'yostocoreowlcarousel'],
    function ($) {
        return function (config) {
            if (config.options.animateOut) {
                config.options.animateOut = '' + config.options.animateOut;
            }
            if (config.options.animateIn) {
                config.options.animateIn = '' + config.options.animateIn;
            }
            var element = config.elementClass;

            $(element).owlCarousel(config.options);
            if (config.options.animateIn && config.options.animateIn !== 'none') {
                $.fn.extend({
                    animateCss: function (animationName) {
                        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
                        $(this).addClass('animated ' + animationName).one(animationEnd, function () {
                            $(this).removeClass('animated ' + animationName);
                        });
                    }
                });
                $(element).on('translate.owl.carousel', function (event) {
                    $(this).find(".item").animateCss(config.options.animateIn);
                });
            }

        }
    }
)