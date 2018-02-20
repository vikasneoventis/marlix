
3.0.1 / 2018-02-09
==================

  * Fix method signature

3.0.0 / 2017-10-30
==================

  * Fix handling of location during captures
  * Remove json wrapping as it is now handled in Service class
  * Update to 3.0 of klarna/module-core

2.3.3 / 2017-10-04
==================

  * Bump version in modules.xml for new way of getting module versions

2.3.2 / 2017-09-28
==================

  * Remove dependencies that are handled by klarna/module-core module

2.3.1 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package

2.3.0 / 2017-09-11
==================

  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢

2.2.3 / 2017-08-30
==================

  * Update code with fixes from MEQP2 in preparation for Marketplace release

2.2.2 / 2017-08-22
==================

  * Remove require-dev section as it is handled in core module

2.2.1 / 2017-08-08
==================

  * Add canceling of Magento order, resetting of quote, and additional logging to cancel observer
  * Add reason message to observer events

2.2.0 / 2017-08-04
==================

  * Inspect response from acknowledge call

2.1.0 / 2017-08-03
==================

  * Change error messaging around order not found
  * Add version to composer.json file
  * Add 'Update Payment Status' button to orders in 'payment_review' status

2.0.5 / 2017-07-31
==================

  * Change caching strategy to better handle for batch invoicing

2.0.4 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

2.0.3 / 2017-06-08
==================

  * Change to pass correct store to order line collector to ensure correct classes are used
  * Add reference to KP module to suggest list now that it is released

2.0.2 / 2017-05-23
==================

  * Don't add OM related URLs to v2 API calls as they are already added
  * Add additional logging to cancel observer

2.0.1 / 2017-05-15
==================

  * Change notifcation controller to always return JSON
  * Properly handle notifications for each payment method

2.0.0 / 2017-05-01
==================

  * Move OM references to OM module
  * Move initialize method to Kco module
  * Add support for cancel after invoice (release-auth)
  * Adjust error message to be more concise when 'order not found' occurs
  * Ensure correct logger is injected
  * Fix cancel observer to better handle for pushqueues in Kred
  * Fix tests directory in composer.json
  * Update license header
  * Add method_code to calls to get correct Builder
  * Add update from M1 module
  * Allow overriding response code for push notifications
  * Add Magento Edition to version string
  * Changes to support KP
  * Change OM to dynamically create builder class
  * Add setBuilderType method
  * Update dependency requirements to 2.0
  * Move setting of correct OM to calling function
  * Change code to pull composer package version for UserAgent
  * Change event prefix from kco to klarna
  * Refactor to allow reading order_id from request body when it isn't provided as query parameter
  * Update copyright years
  * Remove references to unused class
  * Change to allow KP for payment method
  * Change user-agent to report as OM instead of KCO_OM
  * Change route URL from kco to klarna to make more generic
  * Fix call to getReservationId()
  * Relocate quote to kco module
  * Remove unneeded preference as it is handled in core module
  * Remove dependencies on kco module
  * Change logic for cancel observer to handle for Kred vs Kasper
  * Add call to set user-agent.  Bump required version of core
  * Add CHANGELOG.md

1.0.2 / 2017-01-13
==================

  * Code cleanup
  * Change StoreInterface to StoreManagerInterface in constructor to solve for 2.1.3 issues
  * Add gitattributes file to exclude items from composer packages
  * Fix cancel request to use reservation ID

1.0.1 / 2016-11-07
==================

  * Bug fix for order not found in Magento issue
  * Reduce number of packages included in dependencies as some are already required in KCO module

1.0.0 / 2016-10-31
==================

  * Initial Commit
