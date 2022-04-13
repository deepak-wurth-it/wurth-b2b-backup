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

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;

class CustomAttributeFilter
{
    private $resourceConnection;

    private $conditionManager;

    private $eavConfig;

    private $storeManager;

    private $aliasResolver;

    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager   = $conditionManager;
        $this->eavConfig          = $eavConfig;
        $this->storeManager       = $storeManager;
        $this->aliasResolver      = $aliasResolver;
    }

    /**
     * Applies filters by custom attributes to base select
     *
     * @param Select $select
     * @param FilterInterface ...$filters
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function apply(Select $select, FilterInterface ...$filters): Select
    {
        $select         = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $attributes     = [];

        foreach ($filters as $filter) {
            $filterJoinAlias = $this->aliasResolver->getAlias($filter);

            $attributeId = $this->getAttributeIdByCode($filter->getField());

            if ($attributeId === null) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid attribute id for field: %s', $filter->getField())
                );
            }

            $attributes[] = $attributeId;

            $select->joinInner(
                [$filterJoinAlias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
                $this->conditionManager->combineQueries(
                    $this->getJoinConditions($attributeId, $mainTableAlias, $filterJoinAlias),
                    Select::SQL_AND
                ),
                []
            );
        }

        if (count($attributes) === 1) {
            // forces usage of PRIMARY key in main table
            // is required to boost performance in case when we have just one filter by custom attribute
            $attribute = reset($attributes);
            $filter    = reset($filters);
            $select->where(
                $this->conditionManager->generateCondition(
                    sprintf('%s.attribute_id', $mainTableAlias),
                    '=',
                    $attribute
                )
            )->where(
                $this->conditionManager->generateCondition(
                    sprintf('%s.value', $mainTableAlias),
                    is_array($filter->getValue()) ? 'in' : '=',
                    $filter->getValue()
                )
            );
        }

        return $select;
    }

    private function getJoinConditions(int $attrId, string $mainTable, string $joinTable): array
    {
        return [
            sprintf('`%s`.`entity_id` = `%s`.`entity_id`', $mainTable, $joinTable),
            $this->conditionManager->generateCondition(
                sprintf('%s.attribute_id', $joinTable),
                '=',
                $attrId
            ),
            $this->conditionManager->generateCondition(
                sprintf('%s.store_id', $joinTable),
                '=',
                (int)$this->storeManager->getStore()->getId()
            ),
        ];
    }

    private function getAttributeIdByCode(string $field): ?int
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);

        return ($attr && $attr->getId()) ? (int)$attr->getId() : null;
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
