
3.1.1 / 2018-01-24
==================

  * Add B2B Support
  * Change API version labels to specify Klarna Checkout

3.0.3 / 2017-10-24
==================

  * Add support for base KCO and OM modules using newer version of Guzzle

3.0.2 / 2017-10-18
==================

  * Seriously, Marketplace is f*(&$ bonkers

3.0.1 / 2017-10-18
==================

  * Remove 'use' statement as probably not needed and maybe this will make Marketplace happy

3.0.0 / 2017-10-04
==================

  * Move some Enterprise functions into base modules in preparation for single Marketplace release

2.3.4 / 2017-10-20
==================

  * Fix error in logging

2.3.3 / 2017-10-04
==================

  * Bump version in module.xml for new way of handling versions in modules

2.3.2 / 2017-09-28
==================

  * Remove dependencies that are handled by klarna/module-core module

2.3.1 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package

2.3.0 / 2017-09-11
==================

  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢

2.2.5 / 2017-09-01
==================

  * Fix floating point issue affecting PPI-62
  * Update code with fixes from MEQP2

2.2.4 / 2017-08-23
==================

  * Fix reference to store

2.2.3 / 2017-08-14
==================

  * Fix to merge street_address2 into street_address1 for v2
  * Change to only split the address field for DACH

2.2.2 / 2017-08-10
==================

  * Change to allow acknowledge of 0,00 orders
  * Add getCaptures method for future use

2.2.1 / 2017-08-09
==================

  * Cast base_discount_amount to double before comparing

2.2.0 / 2017-08-04
==================

  * Inspect response from acknowledge call
  * Add fallback code for initializing checkout

2.1.2 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

2.1.1 / 2017-06-09
==================

  * Replace reference to Kred with v2 in module description
  * Change to pass correct store to order line collector to ensure correct classes are used
  * Fix typo referencing debug logging method

2.1.0 / 2017-05-23
==================

  * Add dispatching of event for merchant URLs for v2 API calls
  * Add additional logging to LogOrderPushNotification observer

2.0.1 / 2017-05-15
==================

  * PPI-269 Move UTF-8 conversion code to Kred module as only relevant to v2 API

2.0.0 / 2017-05-01
==================

  * Remove billing address from create call if API is nortic
  * Add support for new DACH version
  * Fix column name in join clause
  * Add additional logging during errors
  * Display error message returned from Klarna to Magento admin
  * Fix method call to correct one for api config
  * Add API object to observer event
  * Fix setting testdrive mode for PPI-215
  * Remove check on merchant_prefill and have this done in each builder instead
  * Fix initialization of OM when used in admin
  * Refactor to initialize OM
  * Fix logging after moving of config settings
  * Override Klarna_Checkout_Order with new object to return responses for logging
  * Fix initialization of eventManager
  * Rename events
  * Fix pushqueue logic and add count limit
  * Fix tests directory in composer.json
  * Update license header
  * Refactor klarna.xml to use options inside api_version
  * Add check for street_address being set
  * Add method_code to OM calls to get correct Builder
  * Move address split to Kred module
  * Add override of klarna/php-xmlrpc classes to set client_vsn
  * Add override of klarna/checkout classes to set User-Agent
  * Move API credentials to core
  * Add passing of payment method to config lookup
  * Fix validation url
  * Add passing of payment method to configHelper
  * Update for OM changes to support KP
  * Remove preference for BuilderInterface
  * Update dependency requirements to 2.0
  * Move setting of correct OM to calling function
  * Fix for PPI-158 as wrong URLs were specified
  * Update constructor to set prefix to kco for use in events
  * Update copyright years
  * Move parent classes from KCO to Core module
  * Remove suggestion of OM since it is required
  * Remove reference to virtual package
  * Relocate quote to kco module
  * Remove line as Kred does make use of billing address

1.0.5 / 2017-02-03
==================

  * Fix getPlacedOrder call
  * Add CHANGELOG.md

1.0.4 / 2017-01-13
==================

  * Change StoreInterface to StoreManagerInterface in constructor to solve for 2.1.3 issues

1.0.3 / 2016-12-23
==================

  * Remove border radius from create call
  * Add gitattributes file to exclude items from composer packages
  * Add back_to_store_uri field to merchant urls

1.0.2 / 2016-11-18
==================

  * Temporarily require OM via composer as it is needed for Kred to work
  * Add support for border radius in checkout
  * Update composer module description

1.0.1 / 2016-11-11
==================

  * Add check to ensure billing_address is set before trying to set shipping_address
  * Fix for PPI-138 prefill not working in Kred
  * Reduce number of packages included in dependencies as some are already required in KCO module
  * Support for blocking partial capture/refund with discount for Kred

1.0.0-rc3 / 2016-10-28
======================

  * Fix error message reporting for PPI-76
  * Fix table name alias

1.0.0-rc2 / 2016-10-26
======================

  * Change suggest value to a description per spec
  * Update version of virtual package

1.0.0-rc1 / 2016-10-26
======================

  * Update version info on packages
  * Fix call to getTable
  * Fix for PPI-109 refunds failing
  * Fix issue where quote lifetime is not set
  * Fix logger so it can handle when fields are not set on payload
  * Adding empty composer.json to src directory because... Magento
  * Remove region from shipping address
  * Add code to handle for array being logged
  * Change which config setting is used for pushqueue cleaning
  * Add English translations file
  * Code cleanup from phpcs
  * Fix Acknowlege Kred Order observer
  * Finish refactoring code to allow Ordermanagement class for Kred to work in place of KCO Ordermanagement class
  * Fix missing or incorrect class references
  * Fix issues with API call due to null values being sent instead of removing option
  * Update klarna.xml with reference to Ordermanagement class for Kred
  * Code cleanup and adding OM class for Kred
  * Fix merchant_reference numbers
  * Refactor to use traits
  * Refactor builders to cleanup code and remove duplication
  * Cleanup code and fix references for checkoutHelper to be configHelper
  * Add BuilderFactory class to replace usage of ObjectManager
  * Heavy refactor of Helper classes from KCO module
  * Adjust SQL for queue cleanup
  * Fix class name of observer
  * Cleanup logging
  * Add observers
  * Change how order lines are represented
  * Fix to ensure configs reference correct store
  * Change logger back to use generic filename
  * Change to klarna.xml structure
  * Add builder and API
  * Initial conversion of module from M1
  * Add klarna/checkout library
  * Add Klarna XMLRPC library
  * Initial Commit
