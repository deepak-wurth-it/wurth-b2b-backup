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

namespace Mirasvit\Search\Index\Mirasvit\Kb\Article;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Mirasvit / Knowledge Base';
    }

    public function getIdentifier(): string
    {
        return 'mirasvit_kb_article';
    }

    public function getAttributes(): array
    {
        return [
            'name'             => __('Name'),
            'text'             => __('Content'),
            'tags'             => __('Tags'),
            'meta_title'       => __('Meta Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'article_id';
    }

    public function buildSearchCollection(): Collection
    {
        if (!class_exists('Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory')) {
            return [];
        }

        $collectionFactory = ObjectManager::getInstance()
            ->create('Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.article_id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreIdFilter($storeId)
            ->addFieldToFilter('main_table.is_active', 1);

        $articleTagTable = $collection->getResource()->getTable('mst_kb_article_tag');
        $tagTable = $collection->getResource()->getTable('mst_kb_tag');

        $collection->getSelect()->joinLeft(
            ['article_tags' => $articleTagTable],
            "main_table.article_id = article_tags.at_article_id",
            []
        );

        $collection->getSelect()->joinLeft(
            ['tag' => $tagTable],
            "article_tags.at_tag_id = tag.tag_id",
            ['tags' => new \Zend_Db_Expr('group_concat(tag.name)')]
        );

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.article_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.article_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.article_id');

        $collection->getSelect()->group('main_table.article_id');

        return $collection->toArray()['items'];
    }
}
