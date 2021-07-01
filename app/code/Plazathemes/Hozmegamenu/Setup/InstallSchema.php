<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Plazathemes\Hozmegamenu\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY,
		'thumb_nail',
		[
				'type' => 'varchar',
				'label' => 'Thumbnail',
				'input' => 'image',
				 'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
				'required' => false,
				'sort_order' => 50,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'group' => 'General Information',
		]
		);
		
		
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY,
			'is_new',[
					'type' => 'int',
					'label' => 'Is New',
					'input' => 'select',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'sort_order' => 51,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'group' => 'General Information',
				]
		);
		
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Category::ENTITY,
			'is_sale',  [
					'type' => 'int',
					'label' => 'Is Sale',
					'input' => 'select',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'sort_order' => 52,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'group' => 'General Information',
				]
		);
		

		
      
	   $installer = $setup;
 
        $installer->startSetup();
	  $installer->getConnection()->dropTable($installer->getTable('hozmegamenu'));

		/**
		 * Create table Plazathemes_hozmegamenu_hozmegamenu
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('hozmegamenu')
		)->addColumn(
			'hozmegamenu_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'hozmegamenu ID'
		)->addColumn(
			'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => false, 'default' => '1'],
			'hozmegamenu status'
		)
		->addColumn(
			'type_menu',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => false, 'default' => '1'],
			'hozmegamenu type_menu'
		)
		->addColumn(
			'is_home',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => false, 'default' => '1'],
			'hozmegamenu is_home'
		)
		->addColumn(
			'is_mobile',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => false, 'default' => '1'],
			'hozmegamenu is_home'
		)->addColumn(
			'is_new',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu is_new'
		)
		->addColumn(
			'is_sale',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu is_sale'
		)
		->addColumn(
			'is_level',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu Level'
		)->addColumn(
			'is_column',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu Column'
		)->addColumn(
			'items',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu items'
		)->addColumn(
			'is_link',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu is_link'
		)->addColumn(
			'effect',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true, 'default' => ''],
			'hozmegamenu Effect'
		)->addColumn(
			'image',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'hozmegamenu image'
		)->addColumn(
			'store',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => false, 'default' => '0'],
			'store'
		);
		$installer->getConnection()->createTable($table);
		/**
		 * End create table Plazathemes_hozmegamenu_hozmegamenu
		 */

		$installer->endSetup();

    }
		

}

