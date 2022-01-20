<?php

namespace Amasty\Promo\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addBannersToRule($installer);
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addPromoItemsDiscount($installer);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->addControlTaxShippingSettings($installer);
        }

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->objectManager->create(Operation\ChangeFk::class)->execute($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.2', '<')) {
            $this->objectManager->create(Operation\UpgradeSchemaTo242::class)->execute($setup);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addBannersToRule(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_show_gift_images',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Show Gift Images'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_show_gift_images',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Show Gift Images'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addPromoItemsDiscount(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'items_discount',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Promo Items Discount'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'minimal_items_price',
            [
                'type' => Table::TYPE_FLOAT,
                'default' => null,
                'nullable' => true,
                'comment' => 'Minimal Price'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addControlTaxShippingSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'apply_tax',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Apply tax on original price of promo items added for free'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'apply_shipping',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Apply shipping on promo items added for free'
            ]
        );
    }
}
