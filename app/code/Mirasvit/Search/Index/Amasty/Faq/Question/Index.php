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



namespace Mirasvit\Search\Index\Amasty\Faq\Question;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Amasty / FAQ';
    }

    public function getIdentifier(): string
    {
        return 'amasty_faq_question';
    }

    public function getAttributes(): array
    {
        return [
            'title'        => __('Title'),
            'short_answer' => __('Short Answer'),
            'answer'       => __('Full Answer'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'question_id';
    }

    public function buildSearchCollection(): Collection
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Faq\Model\ResourceModel\Question\CollectionFactory');

        /** @var \Amasty\Faq\Model\ResourceModel\Question\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.question_id');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Faq\Model\ResourceModel\Question\CollectionFactory');

        /** @var \Amasty\Faq\Model\ResourceModel\Question\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter([0, $storeId]);

        if ($entityIds) {
            $collection->addFieldToFilter('question_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.question_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('question_id');

        return $collection->toArray()['items'];
    }
}
