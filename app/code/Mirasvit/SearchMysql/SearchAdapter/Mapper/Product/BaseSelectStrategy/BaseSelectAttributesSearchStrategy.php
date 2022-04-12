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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\BaseSelectStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer\SelectContainer;

class BaseSelectAttributesSearchStrategy
{
    private $resource;

    private $storeManager;

    private $scopeResolver;

    private $indexNameResolver;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        IndexScopeResolver $scopeResolver,
        IndexNameResolver $indexNameResolver
    ) {
        $this->resource          = $resource;
        $this->storeManager      = $storeManager;
        $this->scopeResolver     = $scopeResolver;
        $this->indexNameResolver = $indexNameResolver;
    }

    public function createBaseSelect(SelectContainer $selectContainer): SelectContainer
    {
        $select         = $this->resource->getConnection()->select();
        $mainTableAlias = $selectContainer->isFullTextSearchRequired() ? 'eav_index' : 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        if ($selectContainer->isFullTextSearchRequired()) {
            $tableName = $this->indexNameResolver->getIndexName(
                $selectContainer->getUsedIndex(),
                $selectContainer->getDimensions()
            );

            $select->joinInner(
                ['search_index' => $tableName],
                'eav_index.entity_id = search_index.entity_id',
                []
            )->joinLeft(
                ['ea' => $this->resource->getTableName('eav_attribute')],
                'search_index.attribute_code = ea.attribute_code',
                []
            )->joinLeft(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'cea.attribute_id = ea.attribute_id',
                []
            );
        }

        $selectContainer = $selectContainer->updateSelect($select);

        return $selectContainer;
    }
}
