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



namespace Mirasvit\SearchMysql\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructureInterface;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;

class IndexStructure implements IndexStructureInterface
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

    /**
     * @param string $index
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
        $tableName = $this->indexNameResolver->getIndexName($index, $dimensions);

        if ($this->resource->getConnection()->isTableExists($tableName)) {
            $this->resource->getConnection()->dropTable($tableName);
        }
    }

    /**
     * @param string $index
     * @param array $fields
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $tableName = $this->indexNameResolver->getIndexName($index, $dimensions);

        $this->createFulltextIndex($tableName);
    }

    /**
     * Create fulltext index table.
     */
    private function createFulltextIndex(string $tableName): void
    {
        $table = $this->resource->getConnection()->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Entity ID'
            )->addColumn(
                'attribute_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->addIndex(
                'idx_primary',
                ['entity_id', 'attribute_code'],
                ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
            )->addIndex(
                'FTI_FULLTEXT_DATA_INDEX',
                ['data_index'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );
        $this->resource->getConnection()->createTable($table);
    }
}
