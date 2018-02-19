/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_ProductAttachment
 */
define([
    'jquery'
], function($) {
    return function(e) {
        var dropZone = $('.drop-zone'),
            foundDropzone,
            timeout = window.dropZoneTimeout;

        if (!timeout) {
            dropZone.addClass('in');
        } else {
            clearTimeout(timeout);
        }

        var found = false,
            node = e.target;
        do {
            if ($(node).hasClass('drop-zone')) {
                found = true;
                foundDropzone = $(node);
                break;
            }
            node = node.parentNode;
        } while (node != null);

        dropZone.removeClass('in hover');

        if (found) {
            foundDropzone.addClass('hover');
        }

        window.dropZoneTimeout = setTimeout(function () {
            window.dropZoneTimeout = null;
            dropZone.removeClass('in hover');
        }, 600);
    }
});