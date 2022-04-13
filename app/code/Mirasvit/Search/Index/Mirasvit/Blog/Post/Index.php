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

namespace Mirasvit\Search\Index\Mirasvit\Blog\Post;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Mirasvit / Blog MX';
    }

    public function getIdentifier(): string
    {
        return 'mirasvit_blog_post';
    }

    public function getPrimaryKey(): string
    {
        return 'post_id';
    }

    public function buildSearchCollection(): Collection
    {
        /** @var \Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('\Mirasvit\BlogMx\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()->addVisibilityFilter();
        $this->context->getSearcher()->joinMatches($collection, 'post_id');

        return $collection;
    }

    public function getIndexableDocuments($storeId, $entityIds = null, $lastEntityId = null, $limit = 100): array
    {
        /** @var \Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mirasvit\BlogMx\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()->addVisibilityFilter();

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection->toArray()['items'];
    }

    public function getAttributes(): array
    {
        return [
            'name'             => __('Name'),
            'content'          => __('Content'),
            'short_content'    => __('Short Content'),
            'meta_title'       => __('Meta Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }
}
