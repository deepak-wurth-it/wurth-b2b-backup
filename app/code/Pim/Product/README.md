# Mage2 Module Pim Product

    ``pim/module-product``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Products](#markdown-header-products)


## Main Functionalities
PIM Sync Product Module

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Pim`
 - Enable the module by running `php bin/magento module:enable Pim_Product`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require pim/module-product`
 - enable the module by running `php bin/magento module:enable Pim_Product`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Controller
	- frontend > pimproduct/index/index


## Products

 - Product - Pim Product Id (pim_product_id)

 - Product - Pim Product Sctive Status (pim_product_active_status)

 - Product - Pim Product Code (pim_product_code)

 - Product - Pim Product Parent ID (pim_product_parent_id)

 - Product - Pim Product Channel Id (pim_product_channel_id)

 - Product - Pim Product External Id (pim_product_external_id)

