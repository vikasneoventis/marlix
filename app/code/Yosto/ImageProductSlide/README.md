## Image Product Slide for Magento 2
Image Product Slide make product show more image by slide show.
This extension provide some main function:
    - Display more product image by slide show at category page.
    - Display more product image by slide show at widget.
    - Configuration for display

###Benefits
- Show more product for user without access to detail information.
- Make the product page become more attractive

### To request support:

Feel free to contact us via email: support@x-mage2.com

### Demo version:
You will see the reports at:
Backend: http://x-mage2.com/xmage2admin
username: ipsdemouser
password: xmage2demouser

###1 - Installation

 * Download the extension
 * Unzip the file
 * Copy the content from the unzip folder to {Magento Root}/app/code

####2 -  Enable Extension
 * php -f bin/magento module:enable --clear-static-content Yosto_ImageProductSlide
 * php -f bin/magento setup:upgrade

 Need to refresh cache after enable extension, please use this command when UI error occur
 * ph bin/magento setup:static-content:deploy

####3 - Config Extension

Log into your Magento Admin, then goto Product -> Product Slide Configuration

