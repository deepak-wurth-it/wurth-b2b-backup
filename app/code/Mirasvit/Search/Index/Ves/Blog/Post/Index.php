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

namespace Mirasvit\Search\Index\Ves\Blog\Post;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Ves / Blog';
    }

    public function getIdentifier(): string
    {
        return 'ves_blog_post';
    }

    public function getAttributes(): array
    {
        return [
            'title'            => __('Title'),
            'content'          => __('Content'),
            'short_content'    => __('Short Content'),
            'page_title'       => __('Page Title'),
            'page_keywords'    => __('Page Keywords'),
            'page_description' => __('Page Description'),
            'tags'             => __('Tags'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'post_id';
    }

    public function buildSearchCollection(): Collection
    {
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('\Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexableDocuments($storeId, $entityIds = null, $lastEntityId = null, $limit = 100): array
    {
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $storeManager = $this->context->getObjectManager()
            ->create('Magento\Store\Model\Store');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeManager->load($storeId));

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection->toArray()['items'];
    }
}
