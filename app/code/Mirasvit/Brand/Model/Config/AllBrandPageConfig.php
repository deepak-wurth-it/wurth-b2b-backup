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

namespace Mirasvit\Brand\Model\Config;

use Magento\Store\Model\ScopeInterface;

class AllBrandPageConfig extends BaseConfig
{
    public function isShowBrandLogo(): bool
    {
        return (bool)$this->scopeConfig->getValue('brand/all_brand_page/isShowBrandLogo', ScopeInterface::SCOPE_STORE, $this->storeId);
    }

    public function getMetaTitle(): string
    {
        return (string)$this->scopeConfig->getValue('brand/all_brand_page/MetaTitle', ScopeInterface::SCOPE_STORE, $this->storeId);
    }

    public function getMetaKeyword(): string
    {
        return (string)$this->scopeConfig->getValue('brand/all_brand_page/MetaKeyword', ScopeInterface::SCOPE_STORE, $this->storeId);
    }

    public function getMetaDescription(): string
    {
        return (string)$this->scopeConfig->getValue('brand/all_brand_page/MetaDescription', ScopeInterface::SCOPE_STORE, $this->storeId);
    }
}
