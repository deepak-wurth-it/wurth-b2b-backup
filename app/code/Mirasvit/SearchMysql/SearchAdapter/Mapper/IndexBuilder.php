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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;

class IndexBuilder
{
    private $resource;

    private $indexNameResolver;

    public function __construct(
        ResourceConnection $resource,
        IndexNameResolver $indexNameResolver
    ) {
        $this->resource          = $resource;
        $this->indexNameResolver = $indexNameResolver;
    }

    public function build(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $indexName = $request->getFrom()['index_name'];
        } else {
            $indexName = $request->getIndex();
        }

        $tableName = $this->indexNameResolver->getIndexName(
            $indexName,
            $request->getDimensions()
        );

        $minWeight = 1;
        if (strripos($indexName, 'catalogsearch_fulltext') === false) {
            $minWeight = 2;
        }

        $select = $this->getSelect()
            ->from(
                ['search_index' => $tableName],
                ['entity_id' => 'entity_id']
            )->joinLeft(
                ['cea' => new \Zend_Db_Expr('(SELECT ' . $minWeight . ' as search_weight)')],
                '1=1',
                ''
            );

        return $select;
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->getReadConnection()->select();
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getReadConnection()
    {
        return $this->resource->getConnection();
    }
}
