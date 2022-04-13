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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResourceModel;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;

class StockStatusQueryBuilder
{
    private $stockStatusResourceModel;

    private $conditionManager;

    private $stockConfiguration;

    private $stockRegistry;

    public function __construct(
        StockStatusResourceModel $stockStatusResourceModel,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockStatusResourceModel = $stockStatusResourceModel;
        $this->conditionManager         = $conditionManager;
        $this->stockConfiguration       = $stockConfiguration;
        $this->stockRegistry            = $stockRegistry;
    }

    /**
     * Add stock filter to Select
     *
     * @param Select $select
     * @param string $mainTableAlias
     * @param string $stockTableAlias
     * @param string $joinField
     * @param mixed $values
     * @return Select
     */
    public function apply(Select $select, string $mainTableAlias, string $stockTableAlias, string $joinField, $values = null): Select
    {
        $select->joinInner(
            [$stockTableAlias => $this->stockStatusResourceModel->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    sprintf('%s.product_id = %s.%s', $stockTableAlias, $mainTableAlias, $joinField),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.website_id', $stockTableAlias),
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $values === null
                        ? ''
                        : $this->conditionManager->generateCondition(
                        sprintf('%s.stock_status', $stockTableAlias),
                        is_array($values) ? 'in' : '=',
                        $values
                    ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.stock_id', $stockTableAlias),
                        '=',
                        (int)$this->stockRegistry->getStock()->getStockId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );

        return $select;
    }
}
