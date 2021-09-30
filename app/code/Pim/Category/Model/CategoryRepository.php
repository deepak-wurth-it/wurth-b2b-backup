<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Model;

use Magento\Catalog\Model\CategoryRepository as MagentoCategoryRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepository extends MagentoCategoryRepository
{

    public function getByPimParentId($pimParentId, $storeId = null)
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->instances[$pimParentId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();

            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }

            $category = $category->getCollection()->addAttributeToFilter('pim_category_id', ['in' => $pimParentId]);

            if (!$category->getSize()) {
                return null;
            }

        }
        return $category->getFirstItem()->getId();



    }

    public function getByPimCategoryId($pimCategoryId, $storeId = null)
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->instances[$pimCategoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();

            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }

            $category = $category->getCollection()->addAttributeToFilter('pim_category_id', ['in' => $pimCategoryId]);

            if (!$category->getSize()) {
                return null;
            }

        }
        return $category;



    }


}

