<?php

namespace Wcb\AmastySalesRule\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
           $this->addProductSkuWithDiscountQuantity($installer);
        }
		$installer->endSetup();
    }

    public function addProductSkuWithDiscountQuantity(SchemaSetupInterface $setup)
    {   
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'discount_products_quantity',
            [
                'type' => Table::TYPE_TEXT,
                'default' => 0,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Product Discount Quantity'
            ]
        );
    }

}

