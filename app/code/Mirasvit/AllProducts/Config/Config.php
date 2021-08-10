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

namespace Mirasvit\AllProducts\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    private $scopeConfig;

    private $storeId;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeId     = (int)$storeManager->getStore()->getStoreId();
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_all_products/general/is_enabled',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getUrlKey(): string
    {
        $urlKey = (string)$this->scopeConfig->getValue(
            'mst_all_products/general/url_key',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        return $urlKey ? $urlKey : 'all';
    }

    public function getTitle(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_all_products/general/title',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getMetaTitle(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_all_products/general/meta_title',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getMetaDescription(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_all_products/general/meta_description',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowAllCategories(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'mst_all_products/general/is_show_all_categories',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getMeta(): string
    {
        return (string)$this->scopeConfig->getValue('mst_all_products/seo/robots', ScopeInterface::SCOPE_STORE);
    }
}
