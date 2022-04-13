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



namespace Mirasvit\Search\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier as GenericSearchResultApplier ;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\ConfigProvider;

/**
 * @see Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier::apply()
 */
class SearchResultApplier extends GenericSearchResultApplier
{
    private $collection;

    private $searchResult;

    private $size;

    private $currentPage;

    private $configProvider;

    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage,
        ConfigProvider $configProvider
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->size = $size;
        $this->currentPage = $currentPage;
        $this->configProvider = $configProvider;
        parent::__construct($collection, $searchResult, $size, $currentPage);
    }

    public function apply(): void
    {
        if (!in_array($this->configProvider->getEngine(), ['mysql2', 'sphinx']) || empty($this->searchResult->getItems())) {
            parent::apply();
            return;
        }

        $items = $this->searchResult->getItems();

        $ids = [];
        foreach ($items as $item) {
            $ids[] = (int)$item->getId();
        }

        $this->collection->getSelect()
            ->where('e.entity_id IN (?)', $ids)
            ->reset(\Magento\Framework\DB\Select::ORDER);
        $sortOrder = $this->searchResult->getSearchCriteria()->getSortOrders();

        if (!empty($sortOrder['price']) && $this->collection->getLimitationFilters()->isUsingPriceIndex()) {
            $sortDirection = $sortOrder['price'];
            $this->collection->getSelect()
                ->order(new \Zend_Db_Expr("price_index.min_price = 0, price_index.min_price"), $sortDirection);
        } else {
            $orderList = join(',', $ids);
            $this->collection->getSelect()->order(new \Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
            if (!empty($sortOrder['position'])) {
                $this->collection->getSelect()->order('e.entity_id', 'ASC');
            }
        }

        $this->collection->getSelect()->limit($this->size, $this->getOffset($this->currentPage, $this->size));
    }

    private function getOffset(int $pageNumber, int $pageSize): int
    {
        return ($pageNumber - 1) * $pageSize;
    }
}
