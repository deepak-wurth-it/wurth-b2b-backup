# Mage2 Module Pim Attribute

    ``pim/module-attribute``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
PIM Sync Attribute Module

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Pim`
 - Enable the module by running `php bin/magento module:enable Pim_Attribute`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require pim/module-attribute`
 - enable the module by running `php bin/magento module:enable Pim_Attribute`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Controller
	- frontend > pimattribute/index/index


## Attributes

 - Attribute - Pim Attribute Id (pim_attribute_id)

 - Attribute - Pim Attribute Sctive Status (pim_attribute_active_status)

 - Attribute - Pim Attribute Code (pim_attribute_code)

 - Attribute - Pim Attribute Parent ID (pim_attribute_parent_id)

 - Attribute - Pim Attribute Channel Id (pim_attribute_channel_id)

 - Attribute - Pim Attribute External Id (pim_attribute_external_id)

