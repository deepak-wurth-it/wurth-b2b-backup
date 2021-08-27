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

class UpgradeSchema102
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
            BrandPageInterface::IS_SHOW_IN_BRAND_SLIDER,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show in Brand Slider',
            ]
        );

        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::SLIDER_POSITION,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default' => '10',
                'comment' => 'Slider Position',
            ]
        );

        $connection->addColumn(
            $setup->getTable(BrandPageInterface::TABLE_NAME),
            BrandPageInterface::BRAND_SHORT_DESCRIPTION,
            [
                'type' => Table::TYPE_TEXT,
                'length' => '64K',
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'Brand Short Description',
            ]
        );
    }
}
