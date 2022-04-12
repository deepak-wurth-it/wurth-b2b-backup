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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $keys = [
            'sales_order'      => [
                'created_at',
            ],
            'sales_order_item' => [
                'product_id',
            ],
        ];

        foreach ($keys as $table => $columns) {
            foreach ($columns as $column) {
                if(!$setup->getConnection()->isTableExists($setup->getTable($table))) {
                    continue;
                }
                
                $indexes  = $setup->getConnection()->getIndexList($setup->getTable($table));
                $isExists = false;

                foreach ($indexes as $index) {
                    if (is_array($index['COLUMNS_LIST']) && in_array($column, $index['COLUMNS_LIST'])) {
                        $isExists = true;
                    }
                }
                if ($isExists) {
                    continue;
                }

                $setup->getConnection()->addIndex(
                    $setup->getTable($table),
                    $setup->getConnection()->getIndexName(
                        $setup->getTable($table),
                        [$column]
                    ),
                    [$column]
                );
            }
        }

        $setup->endSetup();
    }
}
