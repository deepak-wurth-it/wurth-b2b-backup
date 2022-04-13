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



namespace Mirasvit\Search\Index\Magento\Cms\Page;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

class Index extends AbstractIndex
{
    protected $collectionFactory;

    public function __construct(
        PageCollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    public function getName(): string
    {
        return 'Magento / Cms Page';
    }

    public function getIdentifier(): string
    {
        return 'magento_cms_page';
    }

    public function getAttributes(): array
    {
        return [
            'title'            => __('Title'),
            'content'          => __('Content'),
            'content_heading'  => __('Content Heading'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'page_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.page_id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);

        $props   = $this->getIndex()->getProperties();
        $ignored = isset($props['ignored_pages']) ? $props['ignored_pages'] : [];
        if (is_array($ignored) && count($ignored)) {
            $collection->addFieldToFilter('identifier', ['nin' => $ignored]);
        }

        if ($entityIds) {
            $collection->addFieldToFilter('page_id', $entityIds);
        }

        $collection
            ->addFieldToFilter('page_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('page_id', 'asc');

        return $collection->toArray()['items'];
    }
}
