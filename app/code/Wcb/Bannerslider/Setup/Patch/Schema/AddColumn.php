<?php
/**
* Copyright © 2021 Wurth. All rights reserved.

* @author Wcb Team
*/

namespace Wcb\Bannerslider\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class AddColumn implements SchemaPatchInterface
{
   private $moduleDataSetup;

   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
   }

   public static function getDependencies()
   {
       return [];
   }

   public function getAliases()
   {
       return [];
   }


   public function apply()
   {
	$installer = $this->moduleDataSetup->startSetup();

	$connection = $installer->getConnection();

	$tableName = $installer->getTable('pt_bannerslider');

	$isTableExist = $connection->isTableExists($tableName);

	if($isTableExist){

		$this->moduleDataSetup->getConnection()->addColumn(
			$this->moduleDataSetup->getTable('pt_bannerslider'),
			'visible_to',
			[
				'type' => Table::TYPE_TEXT,
				'nullable' => true,
				'default' => '',
				'comment' => 'List of Users to Display Banner'
			]
		);
		$this->moduleDataSetup->getConnection()->addColumn(
			$this->moduleDataSetup->getTable('pt_bannerslider'),
			'valid_from',
			[
				'type' => Table::TYPE_TIMESTAMP,
				'nullable' => true,
				'default' => '',
				'comment' => 'Banner Valid From'
			]
		);
		$this->moduleDataSetup->getConnection()->addColumn(
			$this->moduleDataSetup->getTable('pt_bannerslider'),
			'valid_to',
			[
				'type' => Table::TYPE_TIMESTAMP,
				'nullable' => true,
				'default' => '',
				'comment' => 'Banner Valid To'
			]
		);
		$this->moduleDataSetup->getConnection()->addColumn(
			$this->moduleDataSetup->getTable('pt_bannerslider'),
			'display_pages',
			[
				'type' => Table::TYPE_TEXT,
				'nullable' => true,
				'default' => '',
				'comment' => 'Pages to be Displayed'
			]
		);
		}

       $this->moduleDataSetup->endSetup();
   }
}
