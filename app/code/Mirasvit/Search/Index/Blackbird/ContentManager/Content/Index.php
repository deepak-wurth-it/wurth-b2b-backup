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

namespace Mirasvit\Search\Index\Blackbird\ContentManager\Content;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{

    public function getName(): string
    {
        return 'Blackbird / Content Manager';
    }

    public function getIdentifier(): string
    {
        return 'blackbird_contentmanager_content';
    }


    public function getAttributes(): array
    {
        return [
            'title' => __('Title'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    public function buildSearchCollection(): Collection
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create(\Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory::class);

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection
            ->addAttributeToFilter('status', 1)
            ->addAttributeToSelect('*');

        if (count($this->getSearchableTypes())) {
            $collection->addContentTypeFilter($this->getSearchableTypes());
        }

        $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');

        return $collection;
    }

    private function getSearchableTypes(): array
    {
        $types = \Zend_Json::decode($this->getIndex()->getProperty('content_types'));
        $types = is_array($types) ? array_filter($types) : [];

        return $types;
    }

    public function getIndexableDocuments($storeId, $entityIds = null, $lastEntityId = null, $limit = 100): array
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory');

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->addAttributeToSelect('*')
            ->setPageSize($limit)
            ->setOrder('entity_id');

        return $collection->toArray();
    }
}
