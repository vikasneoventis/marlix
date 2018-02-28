# Bring module for magento 2

## Installation instructions
#### Enable module
```bash
composer require trollweb/module-bring
php ./bin/magento module:enable Trollweb_Bring
php ./bin/magento setup:upgrade
php ./bin/magento setup:di:compile
php ./bin/magento setup:static-content:deploy --language nb_NO
```

## Known issues

### Free shipping bug
Steps to reproduce:

1. Add items to cart to obtain cart promo rule with free shipping
2. Go to checkout, get free shipping
3. Remove some items from cart
4. Go to checkout, still get free shipping

This is a bug in magento, see https://github.com/magento/magento2/issues/5332

### Street name not sent in to api 
`$request->getDestStreet()` always returns null. 
This is a bug in magento, see https://github.com/magento/magento2/issues/3789
