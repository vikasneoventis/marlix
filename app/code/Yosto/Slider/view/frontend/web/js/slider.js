/**
 * Created by nghiata on 12/28/2016.
 */
define(
    ['jquery', 'yostocoreowlcarousel'],
    function($){
        return function(config, element) {

            var element = config.config.elementClass;
            $(config.config.elementClass).owlCarousel(config.config.options);

            $.fn.extend({
                animateCss: function (animationName) {
                    var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
                    $(this).addClass('animated ' + animationName).one(animationEnd, function () {
                        $(this).removeClass('animated ' + animationName);
                    });
                }
            });

            var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            var titleActive = $(element + ' .owl-item.active .title');
            if (titleActive) {
                var titleEffect = titleActive.data('effect');
                if(titleEffect != 'none')
                    titleActive.animateCss(titleEffect);
            }
            var subtitleActive = $(element + ' .owl-item.active .subtitle');
            if (subtitleActive) {
                var subtitleEffect = subtitleActive.data('effect');
                if(subtitleEffect != 'none')
                    subtitleActive.animateCss(subtitleEffect);
            }

            var actionActive = $(element + ' .owl-item.active .action');
            if (actionActive) {
                var actionEffect = actionActive.data('effect');
                if(actionEffect != 'none')
                    actionActive.animateCss(actionEffect);
            }


            function onTranslate(event) {
                var indexOfTranslateItem = event.item.index;
                var currentItemActive = $(element + ' .owl-item').get(indexOfTranslateItem);
                currentItemActive = $(currentItemActive);


                var currentTitleActive = currentItemActive.find('.title');
                if (currentItemActive) {
                    var currentTitleEffect = currentTitleActive.data('effect');
                    if(currentTitleEffect != 'none')
                        currentTitleActive.animateCss(currentTitleEffect);
                }

                var currentSubtitleActive = currentItemActive.find('.subtitle');

                if (currentSubtitleActive) {
                    var currentSubtitleEffect = currentSubtitleActive.data('effect');
                    if(currentSubtitleEffect != 'none')
                        currentSubtitleActive.animateCss(currentSubtitleEffect);
                }


                var currentActionActive = currentItemActive.find('.action');
                if (currentActionActive) {
                    var currentActionEffect = currentActionActive.data('effect');
                    if(currentActionEffect != 'none')
                        currentActionActive.animateCss(currentActionEffect);
                }
            }

            function onTranslated(event) {
                $(element + ' .owl-item:not(.active)').attr('class', 'owl-item');
                $(element + ' .owl-item').css('left', '0');
            }


        }
    }
);