<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pim\Product\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
	private $eavSetupFactory;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		ModuleDataSetupInterface $moduleDataSetup
	) {
		$this->eavSetupFactory = $eavSetupFactory;
		$this->moduleDataSetup = $moduleDataSetup;
	}

	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{


		if (version_compare($context->getVersion(), '1.0.6', '<')) {

			$attributesInfoP1 = [

				'product_unit_of_measure_code' => [

					'type' => 'text',
					'label' => 'Product Unit Of Measure Code',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Unit Of Measure Code',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'vendor_name' => [

					'type' => 'text',
					'label' => 'Vendor Name',
					'backend' => '',
					'frontend' => '',
					'label' => 'Vendor Name',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'usage' => [

					'type' => 'text',
					'label' => 'Usage',
					'backend' => '',
					'frontend' => '',
					'label' => 'Usage',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'instructions' => [

					'type' => 'text',
					'label' => 'Instructions',
					'backend' => '',
					'frontend' => '',
					'label' => 'Instructions',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'seo_page_name' => [

					'type' => 'text',
					'label' => 'Seo Page Name',
					'backend' => '',
					'frontend' => '',
					'label' => 'Seo Page Name',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'alternative_name' => [

					'type' => 'text',
					'label' => 'Alternative Name',
					'backend' => '',
					'frontend' => '',
					'label' => 'Alternative Name',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'product_bar_code' => [

					'type' => 'text',
					'label' => 'Product Bar Code',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Bar Code',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				]
			];

			foreach ($attributesInfoP1 as $attributeCode => $attributeParams) {
				$this->moduleDataSetup->getConnection()->startSetup();
				$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
				$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParams);
				$this->moduleDataSetup->getConnection()->endSetup();
			}
		}


		if (version_compare($context->getVersion(), '1.0.9', '<')) {
			$attributesInfoUpdate = [
				'base_unit_of_measure_id' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'vendor_id' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'sales_unit_of_measure_id' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				],
				'abc_group_code' => [
					'	visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				],
				'inventory_item_category_code' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				],
				'minimum_sales_unit_quantity' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'successor_product_code' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'palette_quantity' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'package_box' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'

				],
				'short_name' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				],
				'vendor_item_no' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				],
				'synonyms' => [
					'visible_on_front' => false,
					'attribute_set' => 'Default',
					'group' => 'Product Details'


				]
			];

			foreach ($attributesInfoUpdate as $attributeCode => $attributeParams) {
				$this->moduleDataSetup->getConnection()->startSetup();
				$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
				$eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParams);
				$this->moduleDataSetup->getConnection()->endSetup();
			}
		}

		if (version_compare($context->getVersion(), '1.1.0', '<')) {
			$this->moduleDataSetup->getConnection()->startSetup();
			$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'product_bar_code',
				[
					'type' => 'varchar',
					'label' => 'Product Bar Codes',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Bar Codes',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => '',
					'attribute_set' => 'Default',
					'group' => 'Product Details'
				]
			);

			$this->moduleDataSetup->getConnection()->endSetup();
		}
	}
}
