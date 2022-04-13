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



namespace Mirasvit\SearchMysql\SearchAdapter\Filter;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\Store;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper\VisibilityFilter;

class Preprocessor
{
    private $conditionManager;

    private $scopeResolver;

    private $config;

    private $resource;

    private $connection;

    private $metadataPool;

    private $scopeConfig;

    private $aliasResolver;

    private $customerSession;

    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $scopeResolver,
        Config $config,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig = null,
        AliasResolver $aliasResolver = null,
        Session $customerSession = null
    ) {
        $this->conditionManager = $conditionManager;
        $this->scopeResolver    = $scopeResolver;
        $this->config           = $config;
        $this->resource         = $resource;
        $this->connection       = $resource->getConnection();

        if (null === $scopeConfig) {
            $scopeConfig = ObjectManager::getInstance()
                ->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        }
        if (null === $aliasResolver) {
            $aliasResolver = ObjectManager::getInstance()
                ->get('\Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver');
        }
        if (null === $customerSession) {
            $customerSession = ObjectManager::getInstance()
                ->get('\Magento\Customer\Model\Session');
        }

        $this->scopeConfig     = $scopeConfig;
        $this->aliasResolver   = $aliasResolver;
        $this->customerSession = $customerSession;
    }

    public function process(FilterInterface $filter, bool $isNegation, string $query): string
    {
        return $this->processQueryWithField($filter, $isNegation, $query);
    }

    private function processQueryWithField(FilterInterface $filter, bool $isNegation, string $query): string
    {
        /** @var Attribute $attribute */
        $attribute   = $this->config->getAttribute(Product::ENTITY, $filter->getField());
        $linkIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        if ($filter->getField() === 'price') {
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier('price'),
                $this->connection->quoteIdentifier('price_index.min_price'),
                $query
            );

            $resultQuery .= sprintf(
                ' AND %s = %s',
                $this->connection->quoteIdentifier('price_index.customer_group_id'),
                $this->customerSession->getCustomerGroupId()
            );
        } elseif ($filter->getField() === 'category_ids') {
            return $this->connection->quoteInto(
                'category_ids_index.category_id in (?)',
                $filter->getValue()
            );
        } elseif ($attribute->isStatic()) {
            $alias       = $this->aliasResolver->getAlias($filter);
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier($attribute->getAttributeCode()),
                $this->connection->quoteIdentifier($alias . '.' . $attribute->getAttributeCode()),
                $query
            );
        } elseif ($filter->getField() === VisibilityFilter::VISIBILITY_FILTER_FIELD) {
            return '';
        } elseif ($filter->getType() === FilterInterface::TYPE_TERM &&
            in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'], true)
        ) {
            $resultQuery = $this->processTermSelect($filter, $isNegation);
        } elseif ($filter->getType() === FilterInterface::TYPE_RANGE &&
            in_array($attribute->getBackendType(), ['decimal', 'int'], true)
        ) {
            $resultQuery = $this->processRangeNumeric($filter, $query, $attribute);
        } else {
            $table           = $attribute->getBackendTable();
            $select          = $this->connection->select();
            $ifNullCondition = $this->connection->getIfNullSql('current_store.value', 'main_table.value');

            $currentStoreId = $this->scopeResolver->getScope()->getId();

            $select->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['entity_id'])
                ->join(
                    ['main_table' => $table],
                    "main_table.{$linkIdField} = e.{$linkIdField}",
                    []
                )
                ->joinLeft(
                    ['current_store' => $table],
                    "current_store.{$linkIdField} = main_table.{$linkIdField} AND "
                    . "current_store.attribute_id = main_table.attribute_id AND current_store.store_id = "
                    . $currentStoreId,
                    null
                )
                ->columns([$filter->getField() => $ifNullCondition])
                ->where(
                    'main_table.attribute_id = ?',
                    $attribute->getAttributeId()
                )
                ->where('main_table.store_id = ?', Store::DEFAULT_STORE_ID)
                ->having($query);

            $resultQuery = 'search_index.entity_id IN ('
                . 'select entity_id from  '
                . $this->conditionManager->wrapBrackets($select->__toString())
                . ' as filter)';
        }

        return $resultQuery;
    }

    protected function getMetadataPool(): MetadataPool
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('\Magento\Framework\EntityManager\MetadataPool');
        }

        return $this->metadataPool;
    }

    private function processTermSelect(FilterInterface $filter, bool $isNegation): string
    {
        $alias = $this->aliasResolver->getAlias($filter);
        if (is_array($filter->getValue())) {
            $value = sprintf(
                '%s IN (%s)',
                ($isNegation ? 'NOT' : ''),
                implode(',', array_map([$this->connection, 'quote'], $filter->getValue()))
            );
        } else {
            $value = ($isNegation ? '!' : '') . '= ' . $this->connection->quote($filter->getValue());
        }
        $resultQuery = sprintf(
            '%1$s.value %2$s',
            $alias,
            $value
        );

        return $resultQuery;
    }

    private function processRangeNumeric(FilterInterface $filter, string $query, Attribute $attribute): string
    {
        $tableSuffix = $attribute->getBackendType() === 'decimal' ? '_decimal' : '';
        $table       = $this->resource->getTableName("catalog_product_index_eav{$tableSuffix}");
        $select      = $this->connection->select();
        $entityField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getIdentifierField();

        $currentStoreId = $this->scopeResolver->getScope()->getId();

        $select->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['entity_id'])
            ->join(
                ['main_table' => $table],
                "main_table.{$entityField} = e.{$entityField}",
                []
            )
            ->columns([$filter->getField() => 'main_table.value'])
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ?', $currentStoreId)
            ->having($query);

        $resultQuery = 'search_index.entity_id IN ('
            . 'select entity_id from  '
            . $this->conditionManager->wrapBrackets($select->__toString())
            . ' as filter)';

        return $resultQuery;
    }
}

