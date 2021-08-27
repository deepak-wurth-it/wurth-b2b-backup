<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Pim\Category\Api;
use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
interface CategoryRepositoryInterface extends MagentoCategoryRepositoryInterface
{


    /**
     * Get info about category by Pim ParentId id
     *
     * @param int $pimParentId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByPimParentId($pimParentId, $storeId = null);

}
