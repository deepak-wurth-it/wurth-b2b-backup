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



namespace Mirasvit\SearchAutocomplete\Model\ResourceModel\Catalog;


class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product
{
    private $productIds = [];

    public function prepareSelectStatement(\Magento\Framework\DB\Select $select): \Magento\Framework\DB\Select
    {
        $select->columns('sku');
        $select->where('e.entity_id IN (?)', $this->productIds);

        return $select;
    }

    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }
}
