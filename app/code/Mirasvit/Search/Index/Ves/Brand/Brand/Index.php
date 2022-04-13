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

namespace Mirasvit\Search\Index\Ves\Brand\Brand;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Ves / Brand';
    }

    public function getIdentifier(): string
    {
        return 'ves_brand_brand';
    }

    public function getAttributes(): array
    {
        return [
            'name'             => __('Title'),
            'description'      => __('Content'),
            'page_title'       => __('Page Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'brand_id';
    }

    public function buildSearchCollection(): Collection
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.brand_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexableDocuments($storeId, $entityIds = null, $lastEntityId = null, $limit = 100): array
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.brand_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.brand_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('brand_id');

        return $collection;
    }
}
