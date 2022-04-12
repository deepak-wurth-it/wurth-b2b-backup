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


declare(strict_types=1);

namespace Mirasvit\Search\Index\Magento\Catalog\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Service\ContentService;

class Index extends AbstractIndex
{
    private $collectionFactory;

    private $contentService;

    public function __construct(
        CategoryCollectionFactory $collectionFactory,
        ContentService $contentService,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->contentService    = $contentService;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Magento / Category';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'magento_catalog_category';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create()
            ->addNameToResult()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gt' => 1]);

        if (strpos((string)$collection->getSelect(), '`e`') !== false) {
            $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');
        } else {
            $this->context->getSearcher()->joinMatches($collection, 'main_table.entity_id');
        }

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->context->getStoreManager()->getStore($storeId);

        $root = $store->getRootCategoryId();

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(array_keys($this->getAttributes()))
            ->setStoreId($storeId)
            ->addPathsFilter("1/$root/")
            ->addFieldToFilter('is_active', 1);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('entity_id');

        $collection->getSelect()
            ->joinLeft(
                ['category_product' => $collection->getResource()->getTable('catalog_category_product')],
                'e.entity_id = category_product.category_id',
                ['products_count' => 'count(category_product.product_id)']
            )
            ->group('e.entity_id');

        foreach ($collection as $item) {
            $item->setData('landing_page', $this->renderCmsBlock($item->getData('landing_page'), $storeId));
        }

        $itemsCollection = $collection->toArray();

        foreach ($collection->toArray() as $key => $item) {
            if ($item['products_count'] == 0 && $item['children_count'] == 0) {
                unset($itemsCollection[$key]);
            }
        }

        return $itemsCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return [
            'name'             => __('Name'),
            'description'      => __('Description'),
            'meta_title'       => __('Page Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
            'landing_page'     => __('CMS Block'),
        ];
    }

    /**
     * @param int $blockId
     * @param int $storeId
     *
     * @return string
     */
    protected function renderCmsBlock($blockId, $storeId)
    {
        if ($blockId == 0) {
            return '';
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            /** @var \Magento\Cms\Api\BlockRepositoryInterface $blockRepository */
            $blockRepository = $objectManager->get('Magento\Cms\Api\BlockRepositoryInterface');

            $block = $blockRepository->getById($blockId);

            return $this->contentService->processHtmlContent($storeId, $block->getContent());
        } catch (\Exception $e) {
        }

        return '';
    }
}
