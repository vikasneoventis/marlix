/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

define([], function () {
    /**
     * Convert byte count to float KB/MB format
     *
     * @param int $bytes
     * @return string
     */
    return function(bytes) {
        if (isNaN(bytes)) {
            return '';
        }
        var symbols = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        var exp = Math.floor(Math.log(bytes)/Math.log(2));
        if (exp < 1) {
            exp = 0;
        }
        var i = Math.floor(exp / 10);
        bytes = bytes / Math.pow(2, 10 * i);

        if (bytes.toString().length > bytes.toFixed(2).toString().length) {
            bytes = bytes.toFixed(2);
        }
        return bytes + ' ' + symbols[i];
    };

});