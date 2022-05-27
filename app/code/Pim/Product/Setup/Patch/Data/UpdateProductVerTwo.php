<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pim\Product\Setup\Patch\Data;


use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateProductMetaDescription
 *
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateProductVerTwo implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */

        $attributesInfoUpdate = [
            'base_unit_of_measure_id' => [
                'visible_on_front' => false

            ],
            'vendor_id' => [
                'visible_on_front' => false

            ],
            'sales_unit_of_measure_id' => [
                'visible_on_front' => false
            ],
            'abc_group_code' => [
                'visible_on_front' => false
            ],
            'inventory_item_category_code' => [
                'visible_on_front' => false
            ],
            'minimum_sales_unit_quantity' => [
                'visible_on_front' => false

            ],
            'successor_product_code' => [
                'visible_on_front' => false

            ],
            'palette_quantity' => [
                'visible_on_front' => false

            ],
            'package_box' => [
                'visible_on_front' => false

            ],
            'short_name' => [
                'visible_on_front' => false
            ],
            'vendor_item_no' => [
                'visible_on_front' => false
            ],
            'synonyms' => [

                'visible_on_front' => false

            ],
            'product_code' => [

                'visible_on_front' => false

            ]
        ];
		
		foreach ($attributesInfoUpdate as $attributeCode => $attributeParams) {
			$this->moduleDataSetup->getConnection()->startSetup();
			$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParams);
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
		return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.9';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
