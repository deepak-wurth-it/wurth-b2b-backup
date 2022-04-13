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

namespace Mirasvit\Search\Index\Magefan\Blog\Post;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Magefan / Blog';
    }

    public function getIdentifier(): string
    {
        return 'magefan_blog_post';
    }

    public function getAttributes(): array
    {
        return [
            'title'            => __('Title'),
            'content_heading'  => __('Content Heading'),
            'content'          => __('Content'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'post_id';
    }

    public function buildSearchCollection(): Collection
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Magefan\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Magefan\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id', 'asc');

        return $collection->toArray()['items'];
    }
}
