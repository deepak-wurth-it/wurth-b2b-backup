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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider;

use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider\SelectBuilderForAttribute\ApplyStockConditionToSelect;

class SelectBuilderForAttribute
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ApplyStockConditionToSelect
     */
    private $applyStockConditionToSelect;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->applyStockConditionToSelect = $applyStockConditionToSelect;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    public function build(Select $select, AbstractAttribute $attribute, int $currentScope): Select
    {
        if ($attribute->getAttributeCode() === 'price') {
            /** @var Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }
            $table = $this->resource->getTableName('catalog_product_index_price');
            $select->from(['main_table' => $table], null)
                ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
                ->where('main_table.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
                ->where('main_table.website_id = ?', $store->getWebsiteId());
        } else if ($attribute->getAttributeCode() == 'category_ids') {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
            $table = $this->resource->getTableName('catalog_category_product_index_store' . $currentScopeId);
            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.category_id', 'main_table.product_id']);
            $parentSelect = $this->resource->getConnection()->select();
            $parentSelect->from(['main_table' => $subSelect], ['value' => 'main_table.category_id']);
            $select = $parentSelect;
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
            $table = $this->resource->getTableName(
                'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
            );
            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
                ->distinct()
                ->where('main_table.attribute_id = ?', (int) $attribute->getAttributeId())
                ->where('main_table.store_id = ? ', $currentScopeId);
            if ($this->isAddStockFilter()) {
                $subSelect = $this->applyStockConditionToSelect->execute($subSelect);
            }

            $parentSelect = $this->resource->getConnection()->select();
            $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
            $select = $parentSelect;
        }

        return $select;
    }

    /**
     * Is add stock filter
     *
     * @return bool
     */
    private function isAddStockFilter()
    {
        $isShowOutOfStock = $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );

        return false === $isShowOutOfStock;
    }
}
