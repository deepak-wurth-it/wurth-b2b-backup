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

namespace Mirasvit\Search\Index\Mirasvit\Gry\Registry;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Mirasvit / Gift Registry';
    }

    public function getIdentifier(): string
    {
        return 'mirasvit_gry_registry';
    }

    public function getAttributes(): array
    {
        return [
            'uid'          => __('ID'),
            'name'         => __('Name'),
            'description'  => __('Description'),
            'location'     => __('Event Location'),
            'firstname'    => __('Registrant First Name'),
            'lastname'     => __('Registrant Last Name'),
            'co_firstname' => __('Co-Registrant First Name'),
            'co_lastname'  => __('Co-Registrant Last Name'),
            'email'        => __('Registrant email'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'registry_id';
    }

    public function buildSearchCollection(): Collection
    {
        $collectionFactory = ObjectManager::getInstance()
            ->create('Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        $collection = $collectionFactory->create();
        $collection->addWebsiteFilter()
            ->addIsActiveFilter();

        $uidCollection = $collectionFactory->create();

        $uidCollection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        if ($uidCollection->getSize()) {
            $collection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        } else {
            // Otherwise search only within pulic registries
            $collection->addFieldToFilter('is_public', 1);
        }

        $this->context->getSearcher()->joinMatches($collection, 'main_table.registry_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int  $limit = 100): array
    {
        $websiteId         = $this->context->getStoreManager()->getStore($storeId)->getWebsiteId();
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('main_table.website_id', $websiteId)
            ->addIsActiveFilter();

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.registry_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.registry_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.registry_id');

        return $collection;
    }
}
