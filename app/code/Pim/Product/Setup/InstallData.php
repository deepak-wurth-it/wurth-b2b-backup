<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pim\Product\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		ModuleDataSetupInterface $moduleDataSetup)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->moduleDataSetup = $moduleDataSetup;

	}
	
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


		$attributesInfo = [
            'base_unit_of_measure_id' => [

                'type' => 'text',
                'label' => 'VAT number',
				'backend' => '',
				'frontend' => '',
				'label' => 'Base Unit Of Measure Id',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
            'vendor_id' => [

                'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Vendor Id',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
            'sales_unit_of_measure_id' => [

                'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Sales Unit Of Measure Id',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'
            ],
            'abc_group_code' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Abc Group Code',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
            'inventory_item_category_code' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Inventory Item Category Code',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'
            ],
			'minimum_sales_unit_quantity' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Minimum Sales Unit Quantity',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'successor_product_code' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Successor Product Code',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'palette_quantity' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Palette Quantity',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'package_box' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Package Box',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'short_name' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Short Name',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'vendor_item_no' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Vendor Item No',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

            ],
			'synonyms' => [
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'Synonyms',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => true,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => '',
				'attribute_set'=>'attribute_set',
				'group'=>'Product Details'

			
        ]
	];


		foreach ($attributesInfo as $attributeCode => $attributeParams) {
			$this->moduleDataSetup->getConnection()->startSetup();
			$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
			$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,$attributeCode, $attributeParams);
			$this->moduleDataSetup->getConnection()->endSetup();

        }


	}
}
