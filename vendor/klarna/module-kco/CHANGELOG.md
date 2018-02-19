
3.0.0 / 2017-10-04
==================

  * Move Enterprise support into core module instead of having an add-on

2.3.3 / 2017-10-04
==================

  * Fix check of shipping address different than billing

2.3.2 / 2017-09-28
==================

  * Remove dependencies that are handled by klarna/module-core module

2.3.1 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package

2.3.0 / 2017-09-11
==================

  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢
  * Fix CSS for hiding shipping method from summary area
  * Update code with fixes from MEQP2

2.2.6 / 2017-08-25
==================

  * Fix issue with customer address failing validation during place order
  * Fix to handle for shipping method not being set. Also better array conversion

2.2.5 / 2017-08-24
==================

  * Add try/catch to handle for KcoConfigProvider being called on cart page

2.2.4 / 2017-08-22
==================

  * Refactor to not cancel orders when getting redirect URL fails

2.2.3 / 2017-08-22
==================

  * Remove require-dev section as it is handled by core module

2.2.2 / 2017-08-14
==================

  * Fix nordics/dach check

2.2.1 / 2017-08-10
==================

  * Add support for care_of -> company

2.2.0 / 2017-08-10
==================

  * Reduce the number of quote saves that occur during checkout
  * Save quote using resource model instead of repository
  * Change validate to include a message when redirecting to validateFailed
  * Change to use placeOrder instead of submit. Also removed unneeded code
  * Move dispatch of success event to success controller to avoid any errors from blocking order creation

2.1.3 / 2017-08-09
==================

  * Fix street_address2 handling
  * Add support for house_extension

2.1.2 / 2017-08-08
==================

  * Move canceling of order to observer
  * Hide shipping rate description from side bar

2.1.1 / 2017-08-08
==================

  * If confirmation failed but order was created, cancel order

2.1.0 / 2017-08-04
==================

  * Return response with error message instead of throwing exception
  * Send 302 instead of 301 to avoid caching
  * Add failure_url setting to allow redirecting to somewhere other than the cart

2.0.6 / 2017-07-10
==================

  * Fix error logging

2.0.5 / 2017-07-07
==================

  * Remove duplicate reference to jsonHelper
  * Log exception to klarna logs before throwing it

2.0.4 / 2017-07-05
==================

  * Remove 'google' iframe as it was debugging code

2.0.3 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

2.0.2 / 2017-06-05
==================

  * PPI-303 Fix missing GA code on success page
  * Add more logging to exception handler

2.0.1 / 2017-05-15
==================

  * Remove duplicate config setting

2.0.0 / 2017-05-01
==================

  * Add support for new DACH version
  * Set gender and DOB on customer when creating them via merchant checkbox
  * Remove 'Payment from...' admin settings to resolve PPI-77
  * Move initialize command to KCO module and fix transactionId setting
  * Add support for setting gender on customer
  * Disable editing order to resolve PPI-202
  * Add index to klarna_checkout_id field
  * Remove check on merchant_prefill and have this done in each builder instead
  * Add reporting proper error when a 401 is encountered
  * Fix tests directory in composer.json
  * Update license header
  * Refactor klarna.xml to use options inside api_version
  * Add method_code to event data
  * Add image URL to product item in API call
  * Add more descriptive error message to validation failure
  * Move validateTotal method to CommonController trait in core
  * Refactor to use promise instead of jQuery deferred
  * Handle for EE modules that try to disable module
  * Move Version class to Core module
  * Move credential configs to core module
  * Add dispatch of kco only event
  * Add Magento Edition to version string
  * Update dependency requirements to 2.0
  * Change code to pull composer package version for UserAgent
  * Update constructor to set prefix to kco for use in events
  * Change event to use klarna prefix instead of kco
  * Update copyright years
  * Move orderline classes to Core module
  * Add type cast to int to resolve strict comparison issues
  * Change getActiveByQuote to not save newly created quotes
  * Add missing getId method to interface
  * Fix order of exception handling
  * Remove unused controller
  * Move CommonController triat to core as it is used by multiple modules
  * Relocate quote to kco module
  * Move payment info block to core module
  * Fix PPI-150 by moving when events fire and including order in event
  * Fix shipping_title to be string instead of phrase object
  * Add CHANGELOG.md
  * Update provide version of virtual package
  * Add call to set user-agent.  Bump required version of core

1.1.3 / 2017-01-13
==================

  * Change StoreInterface to StoreManagerInterface in constructor to solve for 2.1.3 issues
  * Update constructor for ApiHelper due to parent class changes
  * Fix tests directory name in gitattributes file

1.1.2 / 2016-12-23
==================

  * Add gitattributes file to exclude items from composer packages
  * Change success page to say 'thank you' instead of 'klarna success' per feedback from Johannes C
  * Add border radius to design section

1.1.1 / 2016-11-11
==================

  * Use correct interface for BC support of M 2.0

1.1.0 / 2016-11-11
==================

  * Set preference for QuoteInterface
  * Rename region for use with DACH module
  * Remove dependency on monolog as not needed since we have psr/log
  * Support for partial capture/refund with discount for Kasper and blocking for Kred
  * Initial porting of partial payment stuff from M1 module

1.0.0-rc3 / 2016-10-29
======================

  * Redirect to 404 if KCO not enabled/allowed

1.0.0-rc2 / 2016-10-27
======================

  * Move shipping methods to sidebar per PPI-98
  * Change suggest value to a description per spec

1.0.0-rc1 / 2016-10-26
======================

  * Fix PPI-116 from using store zip
  * Add getTotals wrapper to suspend/resume iframe
  * Add translation stuff
  * Fix for PPI-83 display totals in sidebar
  * Fix for PPI-103 for dealing with downloadable products
  * Add call to getTotals to reloadContainer
  * Remove loader
  * Change jquery to use dollar sign
  * Add call to update Magento with address info
  * Move selectShippingMethod call into action JS
  * Move location of shipping methods
  * Refactor messages into own JS file
  * Fix reload summary to trigger Klarna update
  * Add country lookup controller
  * Fix posting of address
  * Potential fix for PPI-77
  * Allow multi-selects to be 'empty'
  * Add shipping method selection above iframe if shipping-in-iframe is disabled
  * Fix external payments only working if enabled at default level for PPI-75
  * Fix for PPI-75 external payments
  * Add support for allow/deny guest checkout independent of Magento setting
  * Add guest user to group list
  * Fix missing method call for PPI-68
  * Fix multiselect options.  Also fixes PPI-69
  * Change how logged in check occurs
  * Refactor common code into a Trait for controllers
  * Fix issue with customer being logged out in backend
  * Fix for customer not exists during merchant checkbox create account validation observer
  * Fix for prepopulating addresses
  * Fix for logged in customer checkout
  * Remove static references to ObjectManager
  * Remove check for email belonging to registered user to avoid errors when checking out as guest
  * Fix module name reference
  * Get config for store instead of default
  * Update validate method to make more sense.  Should also fix PPI-58
  * Fix for entity not set error on place order
  * Change handling of customer in session/quote partial fix of PPI-58
  * Change comparison to allow for difference in data type
  * Fix for PPI-61 and simplified fix for PPI-59
  * Fix for PPI-59 (issue with AssociateGuestOrderWithRegisteredCustomer observer)
  * Update translations
  * Add English translations file
  * Allow cancel of payment
  * Override getOptions call to add in Kasper specific options
  * Update user agent
  * Fix tax calculation in Magento sidebar as well as random address overwrite bug
  * Fix retrieveAddress AJAX call
  * Fix duplicate shipping rates
  * Fix shipping rate calculations
  * Fix merchant_reference numbers
  * Refactor to use traits
  * Refactor builders to cleanup code and remove duplication
  * Add BuilderFactory class to replace usage of ObjectManager
  * Heavy refactor of Helper classes
  * Default push and notification URLs to disabled but allow override via event
  * Move notification controller to OM
  * Move push controller to OM
  * Add check for missing country_id back
  * Fix location of referenced CONST
  * Fix for discount showing in order line for PPI-32
  * Change to ensure store is passed to default country lookup
  * Change OM to be loaded via DI instead of using ObjectManager
  * Fix issue with duplicate shipping methods
  * Fix comparison of 'unselected' state
  * Add messages block to checkout
  * Change how order lines are represented
  * Change to klarna.xml structure
  * Remove DI reference as no longer relevant
  * Updates for Kred support
  * Fix address lookup
  * Throw exception if problem loading checkout.  Should only show when in developer mode
  * Fix for module running in multiple stores with different API endpoints
  * Remove reference to type variable
  * Fix for class that was migrated and refactored in core module
  * Fix year
  * Add virtual package to provide list
  * Move om module from require to suggest
  * More migration of classes from kco to om module
  * Additional refacoring to move classes to OM and Core modules
  * Refactor base service class into separate module and remove OM
  * Fix XML for Magento 2.0.x
  * Fix for EventManager in Magento 2.0.x
  * Fix error message to use correct syntax for variable substitution
  * Create klarnacheckout template and change checkout pages to use it
  * Refactor code to work better with Magento 2
  * Change from use statement to fully qualified name to avoid name collision
  * Remove isDefault method call as does not exist in Magento 2.0
  * Fix missing import
  * Refactor payment method to work with both Magento 2.0 and Magento 2.1
  * Fix to work on correct array
  * Refactor to implement code instead of extending core class since class is moved between 2.0 and 2.1
  * Refactor so that order confirmation email works
  * Clear cart on success page
  * Fix 404 issue as class had wrong name internally
  * Add discount to item
  * Refactor to use CONST for version in API URLs
  * Fix error in notification callback controller
  * Fix capture payment
  * Refactor API logging
  * Handle for empty address a little better
  * Fix fetch transaction info call
  * First pass at refactoring due to ECG code sniffs
  * Add ECG coding standards to composer.json
  * Cleanup payment capture functionality
  * Fix observers
  * Refactor how merchant checkboxes are handled
  * Refactor payment method into Command objects
  * Refactor success controller to match closer to Onepage success controller
  * Fix iframe totals not calculating correctly
  * Fix checkout to update summary with shipping info
  * Minor bug fixes and cleanup
  * Rewrite JS to use requirejs and knockoutjs
  * Remove duplicate title
  * Fix shipping method update
  * Fix 'empty cart' issue by looking up quote in API controllers instead of taking from checkoutSession
  * Refactor API controllers as access to checkout helper is needed in all of them
  * Add error message if klarna checkout can't load
  * Payment info block in order view
  * Add Success page
  * Move interfaces to Api namespace
  * Fix quote table name for FK reference
  * Fix dom processor to handle for copyright notice in XML comment
  * Fix missing copyright notices
  * Initial working checkout
  * Order Management API
  * Refactoring to use guzzel client
  * Inject klarna config into Orderline collector
  * Add injection of module verison number into user-agent
  * Add guzzle based rest client
  * Remove old rest client classes
  * Add converted backend classes
  * Update existing classes
  * Add custom logger
  * Add all events/observers
  * Add all controllers
  * Update routes file
  * Move sales_quote_save_before event to global scope
  * Move event.xml to frontend area
  * Update module dependencies
  * Rename Exception class
  * Update Klarna Checkout controller
  * System config sections
  * Update class names in klarna.xml file
  * Working klarna.xml config stuff
  * Fix klarna config file loading
  * Add klarna_check_if_quote_has_changed Observer
  * Add generic Exception object
  * Models for order & quote tables
  * Inject klarna config into helpers
  * Converted helper
  * Fix issue with empty cart detection
  * Converted Checkout helper from M1 module
  * Add checkout.js to Klarna Checkout page
  * Create tables
  * Update source/backend models and enable payments section
  * Add converted config source models from M1
  * Add setup for reading klarna.xml custom config file
  * Add observer to force redirect to /checkout/klarna
  * Move Klarna checkout to it's own URL allowing for A/B testing
  * Add back methods required for KO to work
  * Initial add of iframe to checkout
  * Initial Commit
