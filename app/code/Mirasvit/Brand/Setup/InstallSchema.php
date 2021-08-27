<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Brand Page Table
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(BrandPageInterface::TABLE_NAME)
        )
            ->addColumn(
                BrandPageInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Brand Page Id'
            )->addColumn(
                BrandPageInterface::ATTRIBUTE_OPTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => '0'],
                'Brand Option Id'
            )->addColumn(
                BrandPageInterface::ATTRIBUTE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => '0'],
                'Brand Id'
            )->addColumn(
                BrandPageInterface::IS_ACTIVE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Brand Id'
            )->addColumn(
                BrandPageInterface::URL_KEY,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Url Key'
            )->addColumn(
                BrandPageInterface::LOGO,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Logo'
            )->addColumn(
                BrandPageInterface::BRAND_TITLE,
                Table::TYPE_TEXT,
                '64K',
                ['unsigned' => false, 'nullable' => true],
                'Brand title'
            )->addColumn(
                BrandPageInterface::BRAND_DESCRIPTION,
                Table::TYPE_TEXT,
                '64K',
                ['unsigned' => false, 'nullable' => true],
                'Brand description'
            )->addColumn(
                BrandPageInterface::META_TITLE,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Meta title'
            )->addColumn(
                BrandPageInterface::KEYWORD,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Keyword'
            )->addColumn(
                BrandPageInterface::META_DESCRIPTION,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Meta description'
            )->addColumn(
                BrandPageInterface::ROBOTS,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Robots'
            )->addColumn(
                BrandPageInterface::CANONICAL,
                Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true],
                'Canonical'
            )->addIndex(
                $installer->getIdxName(
                    $installer->getTable(BrandPageInterface::TABLE_NAME),
                    [BrandPageInterface::BRAND_TITLE],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [BrandPageInterface::BRAND_TITLE],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );
        $installer->getConnection()->createTable($table);

        /**
         * Brand Page Store Table
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(BrandPageStoreInterface::TABLE_NAME)
        )
            ->addColumn(
                BrandPageStoreInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                BrandPageStoreInterface::BRAND_PAGE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Brand Page Id'
            )->addColumn(
                BrandPageStoreInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(BrandPageStoreInterface::TABLE_NAME, [BrandPageStoreInterface::STORE_ID]),
                [BrandPageStoreInterface::STORE_ID]
            )->addIndex(
                $installer->getIdxName(BrandPageStoreInterface::TABLE_NAME, [BrandPageStoreInterface::BRAND_PAGE_ID]),
                [BrandPageStoreInterface::BRAND_PAGE_ID]
            )->addForeignKey(
                $installer->getFkName(
                    BrandPageStoreInterface::TABLE_NAME,
                    BrandPageStoreInterface::STORE_ID,
                    BrandPageStoreInterface::TABLE_STORE,
                    BrandPageStoreInterface::STORE_ID
                ),
                BrandPageStoreInterface::STORE_ID,
                $installer->getTable(BrandPageStoreInterface::TABLE_STORE),
                BrandPageStoreInterface::STORE_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    BrandPageStoreInterface::TABLE_NAME,
                    BrandPageStoreInterface::BRAND_PAGE_ID,
                    BrandPageInterface::TABLE_NAME,
                    BrandPageInterface::ID
                ),
                BrandPageInterface::ID,
                $installer->getTable(BrandPageInterface::TABLE_NAME),
                BrandPageInterface::ID,
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);
    }
}
