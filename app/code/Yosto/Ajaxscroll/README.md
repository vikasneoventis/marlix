# Ajaxscroll and Lazy Loading
E-commerce websites have a lot of image and product. If all resource is loaded at the same time, website is very slow.
We knew that customers is not patient, if your website is not fast enough. customers will leave.
An e-commerce website can not exist if customer have to wait so long for loading all data.To resolve that issues, a popular solution is lazy loading.
It increases website performance and make website faster. This extension supports both image lazy loading and ajax scroll for product list.


Features
-Lazy loading for all product images:
+ User can enable or disable this feature
+ Change loading image .gif
+ Able to define objects which are applied by css classes
- Ajax scroll for product list
+ User can enable or disable this feature
+ Able to select what pages are applied: Homepage, category page, search result page, tag page
+ Enable or disable "load more items" button, support change button style (text color, background color) and text (button text)
+ Able to change "loading bar" image (.gif file)
-Back to top button
+ Enable or disable button
+ Support both image and text for button
+ Able to upload image or change style of button


Installation
- Downloads the extension
- Unzip the file
- Open folder "Lazy loading and ajax scroll"
- Copies all content to {Magento 2 Root Folder}
- In most cases, this extension will work well. In special case, you are using an custom theme, and theme author overide product image template file named "image_with_borders.phtml". You need to contact to him for more information, or send a request to integrate new feature.

To enable extension:
- php -f bin/magento setup:upgrade
- php bin/magento setup:static-content:deploy


Request Support
- Feel free to get support via email: support@x-mage2.com