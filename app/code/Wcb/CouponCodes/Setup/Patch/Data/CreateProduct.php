<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CouponCodes\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
 
class CreateProduct implements DataPatchInterface, PatchRevertableInterface
{
 
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;
 
    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try{
 
            $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
             
            $objectManager = $bootstrap->getObjectManager();
             
             
            $appState = $objectManager->get('Magento\Framework\App\State');
             
            $appState->setAreaCode('frontend');
             
             
            $product = $objectManager->create('Magento\Catalog\Model\Product');
             
            $sku = 'FREE';  // set your sku
             
            $product->setSku($sku);
             
            $product->setName('Free Shipping Product'); // set your Product Name of Product
             
            $product->setAttributeSetId(4); // set attribute id
             
            $product->setStatus(1); // status enabled/disabled 1/0
             
            $product->setWeight(1); // set weight of product
             
            $product->setVisibility(4); // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
             
            $product->setWebsiteIds(array(1));
             
            $product->setTaxClassId(0); // Tax class ID
             
            $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
             
            $product->setPrice(20); // set price of product
             
            $product->setStockData(
             
                  array(
             
                  'use_config_manage_stock' => 0,
             
                  'manage_stock' => 1,
             
                  'is_in_stock' => 1,
             
                  'qty' => 100
                  )
                );
             
            $product->save();
             
            $categoryIds = array('2','3'); // assign your product to category using Category Id
             
                $category = $objectManager->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
             
            $category->assignProductToCategories($sku, $categoryIds);
             
            echo "$sku Product Created Successfully ";
             
            }
             
            catch(\Exception $e){
             
            print_r($e->getMessage());
             
            }
     //   $this->moduleDataSetup->getConnection()->endSetup();
    }
 
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'customer_code');
 
        $this->moduleDataSetup->getConnection()->endSetup();
    }
 
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
 
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
         
        ];
    }
}