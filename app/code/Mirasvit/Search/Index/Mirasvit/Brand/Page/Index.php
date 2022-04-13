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

namespace Mirasvit\Search\Index\Mirasvit\Brand\Page;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\Index\AbstractIndex;

/**
 * @SuppressWarnings(PHPMD)
 */
class Index extends AbstractIndex
{
    public function getName(): string
    {
        return 'Mirasvit / Brand';
    }

    public function getIdentifier(): string
    {
        return 'mirasvit_brand_page';
    }

    public function getPrimaryKey(): string
    {
        return 'brand_page_id';
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function buildSearchCollection(): Collection
    {
        /** @var \Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory $brandCollection */
        $brandCollectionFactory = ObjectManager::getInstance()
            ->create('\Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory');

        $brandCollection = $brandCollectionFactory->create();

        $this->context->getSearcher()->joinMatches($brandCollection, 'brand_page_id');

        return $brandCollection;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getIndexableDocuments(int $storeId,array $entityIds = null,int $lastEntityId = null,int $limit = 100): array
    {
        /** @var \Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory $brandCollection */
        $brandCollectionFactory = ObjectManager::getInstance()
            ->create('\Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory');

        $brandCollection = $brandCollectionFactory->create();

        $brandRepository = ObjectManager::getInstance()
            ->create('\Mirasvit\Brand\Repository\BrandRepository');

        if ($entityIds) {
            $brandCollection->addFieldToFilter('brand_page_id', ['in' => $entityIds]);
        }

        $brandCollection->addFieldToFilter('brand_page_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('brand_page_id', 'asc');

        $skipIndexing = [];
        $i = 1;

        foreach ($brandCollection as $brand) {
            if (empty($brand->getBrandName())) {
                $item = $brandRepository->get($brand->getAttributeOptionId());
                if ($item) {
                    $brand->setBrandName($item->getLabel());
                } else {
                    $skipIndexing[] = $i;
                }
            }
            $i++;
        }

        $brandArray = $brandCollection->toArray()['items'];

        foreach ($skipIndexing as $skipKey) {
            unset($brandArray[$skipKey]);
        }

        return $brandArray;
    }

    public function getAttributes(): array
    {
        return [
            'brand_name'        => __('Brand Label'),
            'brand_title'       => __('Page Title'),
            'brand_description' => __('Description'),
            'meta_title'        => __('Meta Title'),
            'meta_keyword'      => __('Meta Keywords'),
            'meta_description'  => __('Meta Description'),
        ];
    }
}
