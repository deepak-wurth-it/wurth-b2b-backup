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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider\SelectBuilderForAttribute;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Join stock table with stock condition to select.
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class ApplyStockConditionToSelect
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function execute(Select $select): Select
    {
        $select->joinInner(
            ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
            'main_table.source_id = stock_index.product_id',
            []
        )->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);

        return $select;
    }
}
