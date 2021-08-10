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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Api\Data;

interface BrandPageStoreInterface
{
    const TABLE_NAME = 'mst_brand_page_store';
    const TABLE_STORE = 'store';

    const ID = 'id';
    const BRAND_PAGE_ID = 'brand_page_id';
    const STORE_ID = 'store_id';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getBrandPageId();

    /**
     * @param string $value
     * @return $this
     */
    public function setBrandPageId($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreId($value);
}
