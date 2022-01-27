<?php

namespace Amasty\BannersLite\Model\ResourceModel;

use \Amasty\BannersLite\Api\Data\BannerRuleInterface;

class CategoryProduct extends \Magento\Catalog\Model\ResourceModel\CategoryProduct
{
    /**
     * @param array $bannerRule
     *
     * @return array
     */
    public function getProductIds($bannerRule)
    {
        $adapter = $this->_resources->getConnection();
        $select = $adapter->select()->distinct(true)->from(
            $this->_resources->getTableName('catalog_category_product'),
            ['product_id']
        )->where($adapter->prepareSqlCondition(
            'category_id',
            ['in' => $bannerRule[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]]
        ));

        return $adapter->fetchCol($select);
    }
}
