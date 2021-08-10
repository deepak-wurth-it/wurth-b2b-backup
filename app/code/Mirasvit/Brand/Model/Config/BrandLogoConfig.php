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

class BrandLogoConfig extends BaseConfig
{
    /**
     * {@inheritdoc}
     */
    public function isProductListBrandLogoEnabled()
    {
        return $this->scopeConfig->getValue(
            'brand/brand_logo/isProductListBrandLogoEnabled',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProductListBrandLogoImageWidth()
    {
        $productListBrandLogoImageWidth = $this->scopeConfig->getValue(
            'brand/brand_logo/ProductListBrandLogoImageWidth',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$productListBrandLogoImageWidth) {
            $productListBrandLogoImageWidth = 50;
        }

        return $productListBrandLogoImageWidth;
    }

    public function getProductListBrandLogoTooltip(): string
    {
        $productListBrandLogoTooltipPrepared = '';

        $productListBrandLogoTooltip = (string)$this->scopeConfig->getValue(
            'brand/brand_logo/ProductListBrandLogoTooltip',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if ($productListBrandLogoTooltip) {
            $productListBrandLogoTooltipPrepared = str_replace(',', '<br/>', $productListBrandLogoTooltip);
        }

        return $productListBrandLogoTooltipPrepared;
    }

    /**
     * {@inheritdoc}
     */
    public function isProductPageBrandLogoEnabled()
    {
        return $this->scopeConfig->getValue(
            'brand/brand_logo/isProductPageBrandLogoEnabled',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPageBrandLogoDescription()
    {
        return $this->scopeConfig->getValue(
            'brand/brand_logo/ProductPageBrandLogoDescription',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPageBrandLogoImageWidth()
    {
        $productListBrandLogoImageWidth = $this->scopeConfig->getValue(
            'brand/brand_logo/ProductPageBrandLogoImageWidth',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$productListBrandLogoImageWidth) {
            $productListBrandLogoImageWidth = 30;
        }

        return $productListBrandLogoImageWidth;
    }

    public function getProductPageBrandLogoTooltip(): string
    {
        $productListBrandLogoTooltipPrepared = '';

        $productListBrandLogoTooltip = (string)$this->scopeConfig->getValue(
            'brand/brand_logo/ProductPageBrandLogoTooltip',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if ($productListBrandLogoTooltip) {
            $productListBrandLogoTooltipPrepared = str_replace(',', '<br/>', $productListBrandLogoTooltip);
        }

        return $productListBrandLogoTooltipPrepared;
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltipMaxImageWidth()
    {
        return $this->scopeConfig->getValue(
            'brand/brand_logo/TooltipMaxImageWidth',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
}
