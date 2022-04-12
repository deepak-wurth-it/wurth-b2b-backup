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



namespace Mirasvit\Search\Index\Aheadworks\Blog\Post;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Aheadworks / Blog';
    }

    public function getIdentifier(): string
    {
        return 'aheadworks_blog_post';
    }

    public function getAttributes(): array
    {
        return [
            'title'            => __('Title'),
            'short_content'    => __('Content Heading'),
            'content'          => __('Content'),
            'meta_title'       => __('Meta Title'),
            'meta_description' => __('Meta Description'),
            'tag_names'        => __('Tags'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function buildSearchCollection(): Collection
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create(\Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory::class);

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 'publication');

        $this->context->getSearcher()->joinMatches($collection, 'main_table.id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory');

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('id');

        return $collection->toArray()['items'];
    }
}
