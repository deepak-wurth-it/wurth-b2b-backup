<?php
/**
 * Category data interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Api\Data;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;

/**
 * @api
 */
interface CategoryInterface extends MagentoCategoryInterface
{
    /**
     * @return int|null
     */
    public function getPimCategoryId();

    /**
     * @param int $id
     * @return $this
     */
    public function setPimCategoryId($id);

    /**
     * Get Pim parent category ID
     *
     * @return int|null
     */
    public function getPimCategoryParentId();

    /**
     * Set Pim parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setPimCategoryParentId($parentId);



    /**
     * Check whether Pim category is active
     *
     * @return bool|null
     */
    public function getPimCategoryActiveStatus();

    /**
     * Set whether category Pim is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setPimCategoryActiveStatus($isActive);

  /**
     * Get Pim Category Code
     *
     * @return int|null
     */
    public function getPimCategoryCode();

    /**
     * Set Pim Category Code
     *
     * @param bool $code
     * @return $this
     */
    public function setPimCategoryCode($code);

    /**
     * Get Pim Category Channel Id
     *
     * @return int|null
     */
    public function getPimCategoryChannelId();

    /**
     * Set Pim Category Channel Id
     *
     * @param bool $channelId
     * @return $this
     */
    public function setPimCategoryChannelId($channelId);


    /**
     * Get Pim Category External Id
     *
     * @return int|null
     */
    public function getPimCategoryExternalId();

    /**
     * Set Pim Category External Id
     *
     * @param bool $externalId
     * @return $this
     */
    public function setPimCategoryExternalId($externalId);

   
}
