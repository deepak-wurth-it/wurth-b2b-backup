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



namespace Mirasvit\Search\Index\Magento\Search\Query;

use Magento\Framework\Data\Collection;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

class Index extends AbstractIndex
{
    protected $collectionFactory;

    public function __construct(
        QueryCollectionFactory $collectionFactory,
        Context                $context
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    public function getName(): string
    {
        return 'Magento / Search Terms';
    }

    public function getIdentifier(): string
    {
        return 'magento_search_query';
    }

    public function getAttributes(): array
    {
        return [
            'query_text' => __('Query Text'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'query_id';
    }

    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.query_id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('display_in_terms', 1)
            ->addFieldToFilter('num_results', ['gt' => 0]);

        if ($entityIds) {
            $collection->addFieldToFilter('page_id', $entityIds);
        }

        $collection
            ->addFieldToFilter('query_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('query_id', 'asc');

        return $collection->toArray()['items'];
    }
}
