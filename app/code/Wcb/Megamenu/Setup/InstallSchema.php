<?php

namespace Wcb\Megamenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class InstallSchema implements InstallSchemaInterface
{
 /**
 * Add Secondary Custom Content
 */
 public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
 {
	$installer = $setup;

	$installer->startSetup();

	$connection = $installer->getConnection();

	$tableName = $installer->getTable('cms_page');
	$columnName = 'addintomenu';


	if ($connection->tableColumnExists($tableName, $columnName) === false) {
		$setup->getConnection()->addColumn(
			$setup->getTable('cms_page'),
			'addintomenu',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				'nullable' => true,
				'comment' => 'Include in header menu',
				'default' => 0
			]
		);
	}
	$installer->endSetup();
 }
}