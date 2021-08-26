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

namespace Mirasvit\LayeredNavigation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    const AJAX_PRODUCT_LIST_WRAPPER_ID = 'm-navigation-product-list-wrapper';

    const NAV_REPLACER_TAG = '<div id="m-navigation-replacer"></div>'; //use for filter opener

    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isSeoFiltersEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_seo_filter/general/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getSeoFiltersUrlFormat(): string
    {
        return (string)$this->scopeConfig->getValue('mst_seo_filter/general/url_format', ScopeInterface::SCOPE_STORE);
    }

    public function isAjaxEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_ajax_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getApplyingMode(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/general/filter_applying_mode', ScopeInterface::SCOPE_STORE);
    }

    public function isShowNestedCategories(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_show_nested_categories', ScopeInterface::SCOPE_STORE);
    }

    public function isMultiselectEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_multiselect_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getFilterItemDisplayMode(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/general/filter_item_display_mode', ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayOptionsBackgroundColor(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/general/display_options_background_color', ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayOptionsBorderColor(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/general/display_options_border_color', ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayOptionsCheckedLabelColor(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/general/display_options_checked_label_color', ScopeInterface::SCOPE_STORE);
    }

    public function isOpenFilter(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_open_filter', ScopeInterface::SCOPE_STORE);
    }

    public function isCorrectElasticFilterCount(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_correct_elastic_filter_count', ScopeInterface::SCOPE_STORE);
    }

    public function getSearchEngine(): string
    {
        return (string)$this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
    }

    public function isCategoryFilterVisibleInLayerNavigation(): bool
    {
        return $this->scopeConfig->getValue('catalog/layered_navigation/display_category') ? true : false;
    }
}
