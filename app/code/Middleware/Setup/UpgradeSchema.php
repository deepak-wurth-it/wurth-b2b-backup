<?php

namespace Embitel\Sap\Setup;

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
        $connection = $installer->getConnection();		
		$orderTable = $installer->getTable('sales_order');
		$creditmemoTable = $installer->getTable('sales_creditmemo');

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $installer->getConnection()
                ->addColumn(
					$orderTable,
					'sap_export',
					array("TYPE" => Table::TYPE_SMALLINT,'default' => 0,'nullable' => true,'COMMENT' => "SAP export"),
					null
				);
			$installer->getConnection()
                ->addColumn(
					$orderTable,
					'recon_export',
					array("TYPE" => Table::TYPE_SMALLINT,'default' => 0,'nullable' => true,'COMMENT' => "SAP export"),
					null
				);
        }
		$installer->endSetup();
    }

}
