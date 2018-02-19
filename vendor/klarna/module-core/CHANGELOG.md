
2.7.1 / 2017-10-04
==================

  * Change constant to regular field

2.7.0 / 2017-10-04
==================

  * Move Enterprise classes into core module to support single Marketplace release

2.6.0 / 2017-10-04
==================

  * Change the way module versions are retrieved

2.5.5 / 2017-10-02
==================

  * Handle for neither KCO or KP being enabled
  * Allow Magento 2.2.0 to be installed

2.5.4 / 2017-09-28
==================

  * Allow 2.0.16
  * Fix PHPDOC and update imports

2.5.3 / 2017-09-25
==================

  * Allow 2.1.9

2.5.2 / 2017-09-19
==================

  * Remove reference to magento-base, because Marketplace!

2.5.1 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package

2.5.0 / 2017-09-15
==================

  * Add support for bundled products PPI-62

2.4.0 / 2017-09-11
================

  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢

2.3.0 / 2017-08-30
==================

  * Fix conflict dependency to comply with Marketplace logic but still block 2.0.11 and 2.1.3
  * Update code with fixes from MEQP2 to prepare for Marketplace release
  * Add check for countries requiring region that shouldn't
  * Refactor tax calculations for discount lines

2.2.5 / 2017-08-25
==================

  * Fix to handle for customer default shipping/billing address

2.2.4 / 2017-08-22
==================

  * Bump require-dev version of pdepend

2.2.3 / 2017-08-15
==================

  * Allow 2.1.8 to be installed

2.2.2 / 2017-08-10
==================

  * Change to ensure using street_address2 instead of street_address_2

2.2.1 / 2017-08-10
==================

  * Add additional block to prevent early upgrading of Magento

2.2.0 / 2017-08-04
==================

  * Add ability to pass context to logger

2.1.1 / 2017-08-03
==================

  * Add code to handle for when Klarna order is not set

2.1.0 / 2017-08-02
==================

  * Add failure_url lookup
  * Add admin CSS file and load it in Stores->Configuration section
  * Add warning message in admin panel for misconfigured settings per PPI-319

2.0.6 / 2017-07-11
==================

  * Fix missing import in CommonController trait

2.0.5 / 2017-07-10
==================

  * Change labels to make it more understandable cross-market what the values should be
  * Fix sort order of config settings
  * Add getPackage method to VersionInfo
  * Change all trait properties/methods to public in CommonController

2.0.4 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

2.0.3 / 2017-06-09
==================

  * Change to pass correct store to order line collector to ensure correct classes are used
  * Change composer setup to block upgrades to Magento until supported by Klarna

2.0.2 / 2017-05-17
==================

  * Fix issue with checking country on a null object
  * PPI-281 Add workaround for class rename/replace done in Magento 2.1.3

2.0.1 / 2017-05-15
==================

  * Log exception to request response
  * PPI-269 Move UTF-8 conversion code to Kred module as only relevant to v2 API
  * Change region to 2 letter code if country is US for PPI-267

2.0.0 / 2017-05-01
==================

  * Add support for multibyte characters
  * Change code to replace non-UTF8 characters in sku and name with question marks for PPI-218
  * Set store on product before pulling product URL
  * Move methods from discount line to abstract
  * Add plugin to set invoice_id on credit memo
  * Cast discount title to string before sending to API
  * Port over tax rate stuff from M1 for PPI-177
  * Handle for apply tax before discount
  * Handle for tax on discount when not using a separate tax line
  * Handle for when orderline is processed by OM
  * Fix DOB on prefill
  * Disable editing order to resolve PPI-202
  * Add passing of store to config check
  * Display Klarna logo instead of plain text in admin
  * Remove check on merchant_prefill and have this done in each builder instead
  * Fix scope setting for stores
  * Add status code and message to response array and throw exception when status is 401
  * Fix for PPI-185 not sending colors for KP
  * Fix tests directory in composer.json
  * Update license header
  * Refactor klarna.xml to use options inside api_version
  * Refactor code to better handle for which builder is used by OM
  * Move address split into Kred module
  * Add product image URL to API call
  * Add product URL to API call
  * Move API credentials to core module
  * Change logger to support enabling per store
  * Add getApiConfig and getApiConfigFlag methods
  * Fix shipping reference to match shipping method code
  * Add is_acknowledged setter/getter to interface and implementation for order
  * Split Magento Edition out of version string
  * Add getOmBuilderType method
  * Move version info into it's own class
  * Update copyright years
  * Refactor to abstract processing of klarna.xml
  * Add handling of payments_order_lines in klarna.xml
  * Move orderlines from KCO to Core module
  * Add reading from kp's klarna.xml file
  * Refactor to properly handle KP vs KCO payment methods
  * Add preferences for Order and OrderRepository interfaces
  * Fix create API call to not set the street_address field for DE markets
  * Move CommonController trait to core as it is used by multiple modules
  * Add preference for service class
  * Relocate quote to kco module
  * Fix missing methods from interface
  * Update BuilderInterface for KP support
  * Remove unused dependencies
  * Move payment info block to core module
  * Rename order table and add session_id column
  * Fix PPI-149 merchant checkbox text not being sent in API call
  * Add override of user-agent from Guzzle client
  * Update interface and implementation classes
  * Fix to create quote if one doesn't exist
  * Add getPaymentConfigFlag method
  * Refactor class to be more generic to add support for KP
  * Change how loading of quote works
  * Change how delete of quote works
  * Add SaveHandler
  * Add member fields for db caching
  * Remove getList method as unused
  * Add gitattributes file to exclude certain files from composer
  * Add CHANGELOG.md
