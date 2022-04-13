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

namespace Mirasvit\Search\Index\Fishpig\Glossary\Word;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'FishPig / Glossary';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'fishpig_glossary_word';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return [
            'word'             => __('Word'),
            'short_definition' => __('Short Definiton'),
            'definition'       => __('Definition'),
            'meta_title'       => __('Meta Title'),
            'meta_description' => __('Meta Description'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey(): string
    {
        return 'word_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection(): Collection
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('FishPig\Glossary\Model\ResourceModel\Word\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.word_id');

        return $collection;
    }

    /**
     * @param int       $storeId
     * @param array     $entityIds
     * @param int       $lastEntityId
     * @param int       $limit
     */
    public function getIndexableDocuments($storeId, $entityIds = null, $lastEntityId = null, $limit = 100): array
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('FishPig\Glossary\Model\ResourceModel\Word\CollectionFactory');

        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('word_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('word_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('word_id');

        return $collection;
    }
}
