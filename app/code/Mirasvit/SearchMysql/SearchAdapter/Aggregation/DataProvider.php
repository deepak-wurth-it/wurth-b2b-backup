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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\StoreManager;
use Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider\SelectBuilderForAttribute;

class DataProvider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory|null
     */
    private $dimensionFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;

    /**
     * @var DataProvider\DataProvider
     */
    private $dataProvider;

    public function __construct(
        ResourceConnection $resource,
        Range $range,
        Session $customerSession,
        DataProvider\DataProvider $dataProvider,
        IntervalFactory $intervalFactory,
        StoreManager $storeManager,
        IndexScopeResolverInterface $priceTableResolver,
        DimensionFactory $dimensionFactory,
        Config $eavConfig,
        ScopeResolverInterface $scopeResolver,
        SelectBuilderForAttribute $selectBuilderForAttribute
    ) {
        $this->resource           = $resource;
        $this->connection         = $resource->getConnection();
        $this->range              = $range;
        $this->customerSession    = $customerSession;
        $this->dataProvider       = $dataProvider;
        $this->intervalFactory    = $intervalFactory;
        $this->storeManager       = $storeManager ? : ObjectManager::getInstance()->get(StoreManager::class);
        $this->priceTableResolver = $priceTableResolver ? : ObjectManager::getInstance()->get(
            IndexScopeResolverInterface::class
        );
        $this->dimensionFactory   = $dimensionFactory ? : ObjectManager::getInstance()->get(DimensionFactory::class);

        $this->eavConfig                 = $eavConfig;
        $this->scopeResolver             = $scopeResolver;
        $this->selectBuilderForAttribute = $selectBuilderForAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(\Magento\Framework\Search\Dynamic\EntityStorage $entityStorage)
    {
        $aggregation = [
            'count' => 'count(main_table.entity_id)',
            'max'   => 'MAX(min_price)',
            'min'   => 'MIN(min_price)',
            'std'   => 'STDDEV_SAMP(min_price)',
        ];

        $select          = $this->getSelect();
        $websiteId       = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $tableName = $this->resource->getTableName('catalog_product_index_price');
        //            $this->priceTableResolver->resolve(
        //            'catalog_product_index_price',
        //            [
        //                $this->dimensionFactory->create(
        //                    WebsiteDimensionProvider::DIMENSION_NAME,
        //                    (string)$websiteId
        //                ),
        //                $this->dimensionFactory->create(
        //                    CustomerGroupDimensionProvider::DIMENSION_NAME,
        //                    (string)$customerGroupId
        //                ),
        //            ]
        //        );

        /** @var Table $table */
        $table = $entityStorage->getSource();
        $select->from(['main_table' => $tableName], [])
            ->where('main_table.entity_id in (select entity_id from ' . $table->getName() . ')')
            ->columns($aggregation);

        $select->where('customer_group_id = ?', $customerGroupId);
        $select->where('main_table.website_id = ?', $websiteId);

        return $this->connection->fetchRow($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());

        return $this->intervalFactory->create(['select' => $select]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());
        $column = $select->getPart(Select::COLUMNS)[0];
        $select->reset(Select::COLUMNS);
        $rangeExpr = new \Zend_Db_Expr(
            $this->connection->getIfNullSql(
                $this->connection->quoteInto('FLOOR(' . $column[1] . ' / ? ) + 1', $range),
                1
            )->__toString()
        );

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->group('range')
            ->order('range');
        $result = $this->connection->fetchPairs($select);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice   = $index == $lastIndex ? '' : $index * $range;

                $data[] = [
                    'from'  => $fromPrice,
                    'to'    => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = (int)$this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();
        $attribute    = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());
        $select       = $this->getSelect();

        if ($bucket->getName() == 'category_bucket') {
            $select->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'main_table.product_id  = entities.entity_id',
                []
            );
        } else {
            $select->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'main_table.entity_id  = entities.entity_id',
                []
            );
        }
        //        $this->eventManager->dispatch(
        //            'catalogsearch_query_add_filter_after',
        //            ['bucket' => $bucket, 'select' => $select]
        //        );
        $select = $this->selectBuilderForAttribute->build($select, $attribute, $currentScope);

        return $select;
    }

    /**
     * @inheritdoc
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }
}
