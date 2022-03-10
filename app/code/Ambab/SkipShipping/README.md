# Ambab SkipShipping

This extension enables the system to skip the shipping method from the cart and checkout step when there is only one shipping method enabled from the admin backend. 

## Features

- Hide shipping method from cart & checkout page if there is single shipping method.
- Hide Shipping charges from cart & checkout page's order summary
- Easy installation
- 100% Open Source Code

## Installation

Install the extension through composer package manager.

- Go to project root directory
- composer require ambab/module-skipshipping
- bin/magento module:enable Ambab_SkipShipping
- bin/magento setup:upgrade
- bin/magento cache:flush

## Configuration

- Go to Admin -> Stores -> Configuration -> Ambab -> SkipShipping
- Go to project root directory
- bin/magento setup:static-content:deploy
- bin/magento cache:flush

## Contribute

Feel free to fork and contribute to this module by creating a PR to develop branch.

## Support

Please feel free to reach out at tech.support@ambab.com
