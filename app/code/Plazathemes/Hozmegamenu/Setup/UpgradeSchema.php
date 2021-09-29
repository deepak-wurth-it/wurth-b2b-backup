<?php
namespace Plazathemes\Hozmegamenu\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		echo "Hi Here";$installer = $setup;

        $installer->startSetup();

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

        $installer->endSetup();
	}
}
