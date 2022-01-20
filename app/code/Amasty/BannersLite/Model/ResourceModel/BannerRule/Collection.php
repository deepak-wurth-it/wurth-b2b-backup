<?php

namespace Amasty\BannersLite\Model\ResourceModel\BannerRule;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\BannerRule;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as ResourceModel;
use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(BannerRule::class, ResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param string $productSku
     * @param array $productCats
     *
     * @return array
     */
    public function getValidBannerRuleIds($productSku, $productCats)
    {
        $this->getSelect()->where($this->prepareSqlCondition($productSku, $productCats));

        return $this->getRuleIds();
    }

    /**
     * @param string $productSku
     * @param array $productCats
     *
     * @return string
     */
    private function prepareSqlCondition($productSku, $productCats)
    {
        /* show_banner_for = '0' */
        $sql = $this->getConnection()->prepareSqlCondition(
            BannerRuleInterface::SHOW_BANNER_FOR,
            BannerRuleInterface::ALL_PRODUCTS
        );

        /* show_banner_for = '0' OR (show_banner_for = '1' AND banner_product_sku IN('24-MB03')) */
        if ($productSku) {
            $sql .= ' OR (' . $this->getSkuSql($productSku) . ')';
        }

        /* show_banner_for = '0'
                OR (show_banner_for = '1' AND banner_product_sku IN('24-MB03'))
                OR (show_banner_for = '2'
                    AND (((FIND_IN_SET('3', banner_product_categories))
                            OR (FIND_IN_SET('4', banner_product_categories))))) */
        if ($productCats) {
            $sql .= ' OR (' . $this->getCategorySql($productCats) . ')';
        }

        return $sql;
    }

    /**
     * @param string $productSku
     *
     * @return string
     */
    private function getSkuSql($productSku)
    {
        return $this->getConnection()->prepareSqlCondition(
            BannerRuleInterface::SHOW_BANNER_FOR,
            BannerRuleInterface::PRODUCT_SKU
        ) . ' AND '
            . $this->getConnection()->prepareSqlCondition(
                BannerRuleInterface::BANNER_PRODUCT_SKU,
                ['finset' => $productSku]
            );
    }

    /**
     * @param array $productCats
     *
     * @return string
     */
    private function getCategorySql($productCats)
    {
        $query = $this->getConnection()->prepareSqlCondition(
            BannerRuleInterface::SHOW_BANNER_FOR,
            BannerRuleInterface::PRODUCT_CATEGORY
        ) . ' AND (';

        $conditions = [];
        foreach ($productCats as $category) {
            $conditions[] = ['finset' => $category];
        }
        $query .= $this->getConnection()->prepareSqlCondition(
            BannerRuleInterface::BANNER_PRODUCT_CATEGORIES,
            $conditions
        );
        $query .= ')';

        return $query;
    }

    /**
     * @return array
     */
    private function getRuleIds()
    {
        $select = clone $this->getSelect();

        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $select->reset(Select::COLUMNS);

        $select->columns(BannerRuleInterface::SALESRULE_ID, 'main_table');

        return $this->getConnection()->fetchCol($select, $this->_bindParams);
    }
}
