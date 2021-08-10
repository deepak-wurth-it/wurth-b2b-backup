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

namespace Mirasvit\QuickNavigation\Setup\UpgradeSchema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $tableName = $setup->getTable(SequenceInterface::TABLE_NAME);

        $connection->dropIndex($tableName, $setup->getIdxName(
            $setup->getTable(SequenceInterface::TABLE_NAME),
            [SequenceInterface::SEQUENCE],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        ));

        $connection->changeColumn(
            $tableName,
            SequenceInterface::SEQUENCE,
            SequenceInterface::SEQUENCE,
            [
                'type'   => Table::TYPE_TEXT,
                'length' => 255,
            ]
        );

        $connection->addIndex($tableName, $setup->getIdxName(
            $setup->getTable(SequenceInterface::TABLE_NAME),
            [SequenceInterface::SEQUENCE],
            AdapterInterface::INDEX_TYPE_INDEX
        ), [SequenceInterface::SEQUENCE], AdapterInterface::INDEX_TYPE_INDEX);
    }
}
