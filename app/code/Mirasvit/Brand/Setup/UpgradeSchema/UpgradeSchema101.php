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
namespace Mirasvit\Brand\Setup\UpgradeSchema;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema101
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::BANNER_ALT,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'Banner Alt',
            ]
        );
        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::BANNER_TITLE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'Banner Title',
            ]
        );
        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::BANNER,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'Banner',
            ]
        );
        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::BANNER_POSITION,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'Banner position',
            ]
        );
    }
}
