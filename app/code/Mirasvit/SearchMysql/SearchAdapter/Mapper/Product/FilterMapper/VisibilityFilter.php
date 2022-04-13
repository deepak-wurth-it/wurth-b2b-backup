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

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\StoreManagerInterface;


class VisibilityFilter
{
    const VISIBILITY_FILTER_FIELD = 'visibility';

    const FILTER_BY_JOIN = 'join_filter';

    const FILTER_BY_WHERE = 'where_filter';

    private $resourceConnection;

    private $conditionManager;

    private $storeManager;

    private $eavConfig;

    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        StoreManagerInterface $storeManager,
        EavConfig $eavConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager   = $conditionManager;
        $this->storeManager       = $storeManager;
        $this->eavConfig          = $eavConfig;
    }

    public function apply(Select $select, FilterInterface $filter, string $type): Select
    {
        if ($type !== self::FILTER_BY_JOIN && $type !== self::FILTER_BY_WHERE) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;

        $type === self::FILTER_BY_JOIN
            ? $this->applyFilterByJoin($filter, $select)
            : $this->applyFilterByWhere($filter, $select);

        return $select;
    }

    private function applyFilterByJoin(FilterInterface $filter, Select $select): void
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            ['visibility_filter' => $this->resourceConnection->getTableName('catalog_product_index_eav')],
            $this->conditionManager->combineQueries(
                [
                    sprintf('%s.entity_id = visibility_filter.entity_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.attribute_id',
                        '=',
                        $this->getVisibilityAttributeId()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.value',
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.store_id',
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );
    }

    private function applyFilterByWhere(FilterInterface $filter, Select $select): void
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->where(
            $this->conditionManager->combineQueries(
                [
                    $this->conditionManager->generateCondition(
                        sprintf('%s.attribute_id', $mainTableAlias),
                        '=',
                        $this->getVisibilityAttributeId()
                    ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.value', $mainTableAlias),
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.store_id', $mainTableAlias),
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            )
        );
    }

    private function getVisibilityAttributeId(): int
    {
        $attr = $this->eavConfig->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::VISIBILITY_FILTER_FIELD
        );

        return (int)$attr->getId();
    }

    private function extractTableAliasFromSelect(Select $select): ?string
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
