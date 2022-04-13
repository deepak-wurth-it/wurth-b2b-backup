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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\Manager;
use Magento\Framework\Search\Request\BucketInterface;

class DataProvider
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;

    /**
     * @var Manager
     */
    private $eventManager;

    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        SelectBuilderForAttribute $selectBuilderForAttribute = null,
        Manager $eventManager = null
    ) {
        $this->eavConfig                 = $eavConfig;
        $this->connection                = $resource->getConnection();
        $this->scopeResolver             = $scopeResolver;
        $this->selectBuilderForAttribute = $selectBuilderForAttribute
            ? : ObjectManager::getInstance()->get(SelectBuilderForAttribute::class);
        $this->eventManager              = $eventManager ? : ObjectManager::getInstance()->get(Manager::class);
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

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );
        $this->eventManager->dispatch(
            'catalogsearch_query_add_filter_after',
            ['bucket' => $bucket, 'select' => $select]
        );
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

    /**
     * Get select.
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
