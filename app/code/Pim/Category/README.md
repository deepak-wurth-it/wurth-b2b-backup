# Mage2 Module Pim Category

    ``pim/module-category``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
PIM Sync Category Module

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Pim`
 - Enable the module by running `php bin/magento module:enable Pim_Category`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require pim/module-category`
 - enable the module by running `php bin/magento module:enable Pim_Category`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Controller
	- frontend > pimcategory/index/index


## Attributes

 - Category - Pim Category Id (pim_category_id)

 - Category - Pim Category Sctive Status (pim_category_active_status)

 - Category - Pim Category Code (pim_category_code)

 - Category - Pim Category Parent ID (pim_category_parent_id)

 - Category - Pim Category Channel Id (pim_category_channel_id)

 - Category - Pim Category External Id (pim_category_external_id)

