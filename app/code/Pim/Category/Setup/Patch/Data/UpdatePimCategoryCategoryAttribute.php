<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pim\Category\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpdatePimCategoryCategoryAttribute implements DataPatchInterface, PatchRevertableInterface
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
     * Constructor
     *
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
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_id',
            [
                'label' => 'Pim Category Id'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_external_id',
            [
                'label' => 'Pim Category External Id'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_code',
            [
                'label' => 'Pim Category Code'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_channel_id',
            [
                'label' => 'Pim Category Channel Id'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_active_status',
            [
                'label' => 'Pim Category Active Status'
            ]
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'pim_category_parent_id',
            [
                'label' => 'Pim Category Parent Id'
            ]
        );


        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        return [];
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
        return [];
    }
}
