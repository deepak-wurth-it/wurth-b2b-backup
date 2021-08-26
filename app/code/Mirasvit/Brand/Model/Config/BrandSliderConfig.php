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

class BrandSliderConfig extends BaseConfig
{
    public function getItemsLimit(): int
    {
        $itemsLimit = (int)$this->scopeConfig->getValue(
            'brand/brand_slider/ItemsLimit',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$itemsLimit) {
            $itemsLimit = 4;
        }

        return $itemsLimit;
    }

    public function getOrder(): int
    {
        return (int)$this->scopeConfig->getValue(
            'brand/brand_slider/Order',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowTitle(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isShowTitle',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getTitleText(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/TitleText',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getTitleTextColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/TitleTextColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getTitleBackgroundColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/TitleBackgroundColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowBrandLabel(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isShowBrandLabel',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getBrandLabelColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/BrandLabelColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowButton(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isShowButton',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowPagination(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isShowPagination',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isAutoPlay(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isAutoPlay',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isAutoPlayLoop(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'brand/brand_slider/isAutoPlayLoop',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getAutoPlayInterval(): int
    {
        $autoPlayInterval = (int)$this->scopeConfig->getValue(
            'brand/brand_slider/AutoPlayInterval',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$autoPlayInterval) {
            $autoPlayInterval = 4000;
        }

        return $autoPlayInterval;
    }

    public function getPauseOnHover(): int
    {
        return (int)$this->scopeConfig->getValue(
            'brand/brand_slider/PauseOnHover',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }


    public function getSliderWidth(): int
    {
        return (int)$this->scopeConfig->getValue(
            'brand/brand_slider/SliderWidth',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getSliderImageWidth(): int
    {
        return (int)$this->scopeConfig->getValue(
            'brand/brand_slider/SliderImageWidth',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getSpacingBetweenImages(): int
    {
        $spacingBetweenImages = (int)$this->scopeConfig->getValue(
            'brand/brand_slider/SpacingBetweenImages',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$spacingBetweenImages) {
            $spacingBetweenImages = 10;
        }

        return $spacingBetweenImages;
    }

    public function getInactivePagingColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/InactivePagingColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getActivePagingColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/ActivePagingColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getHoverPagingColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/HoverPagingColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getNavigationButtonsColor(): string
    {
        return (string)$this->scopeConfig->getValue(
            'brand/brand_slider/NavigationButtonsColor',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
}
