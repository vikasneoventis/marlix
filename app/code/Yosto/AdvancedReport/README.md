## Advanced Reports for Magento 2
This extension calculates and analysis data from magento database, then the data will be showed  by charts. Advanced Reports was associated
by two popular chart technologies: nvd3 (based on D3) and Google chart. Main featured functions includes:

- Revenue Reports
- Customer Group Reports
- Geolocation Reports
- Sales Reports
- Bestsellers Reports

This extension allows user filter report as magento 2 base reports.  Support multi-websites, multi-stores.

### To request support:

Feel free to contact us via email: support@x-mage2.com

### Demo version:
You will see the reports at:
Backend: http://x-mage2.com/xmage2admin
username: ardemouser
password: xmage2demouser

###1 - Installation

 * Download the extension
 * Unzip the file
 * Copy the content from the unzip folder to {Magento Root}/app/code

####2 -  Enable Extension
 * php -f bin/magento module:enable --clear-static-content Yosto_AdvancedReport
 * php -f bin/magento setup:upgrade

 Need to refresh cache after enable extension, please use this command when UI error occur
 * ph bin/magento setup:static-content:deploy

####3 - Config Extension

Log into your Magento Admin, then goto Report -> Advanced Reports -> Configuration

