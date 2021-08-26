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

namespace Mirasvit\LayeredNavigation\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class ExtraFilterConfigProvider
{
    const NEW_FILTER                   = 'mst_new_products';
    const ON_SALE_FILTER               = 'mst_on_sale';
    const STOCK_FILTER                 = 'mst_stock_status';
    const IN_STOCK_FILTER              = 2;
    const OUT_OF_STOCK_FILTER          = 1;
    const RATING_FILTER                = 'rating_summary';
    const NEW_FILTER_FRONT_PARAM       = 'mst_new_products';
    const ON_SALE_FILTER_FRONT_PARAM   = 'mst_on_sale';
    const STOCK_FILTER_FRONT_PARAM     = 'mst_stock';
    const RATING_FILTER_FRONT_PARAM    = 'rating';
    const NEW_FILTER_DEFAULT_LABEL     = 'New';
    const ON_SALE_FILTER_DEFAULT_LABEL = 'Sale';
    const STOCK_FILTER_DEFAULT_LABEL   = 'Stock';
    const RATING_FILTER_DEFAULT_LABEL  = 'Rating';

    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isFilterEnabled(string $filter): bool
    {
        $method = 'is' . $this->transformToMethod($filter) . 'FilterEnabled';
        if (!method_exists($this, $method)) {
            throw new LocalizedException(__('Filter type "%1" does not exist', $filter));
        }

        return $this->{$method}();
    }

    public function getFilterPosition(string $filter): int
    {
        $method = 'get' . $this->transformToMethod($filter) . 'FilterPosition';

        if (!method_exists($this, $method)) {
            throw new LocalizedException(__('Filter type "%1" does not exist', $filter));
        }

        return $this->{$method}();
    }

    public function isNewFilterEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/extra_filter/new/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getNewFilterLabel(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/extra_filter/new/label', ScopeInterface::SCOPE_STORE);
    }

    public function getNewFilterPosition(): int
    {
        return (int)$this->scopeConfig->getValue('mst_nav/extra_filter/new/position', ScopeInterface::SCOPE_STORE);
    }

    public function isOnSaleFilterEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/extra_filter/sale/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getOnSaleFilterLabel(): string
    {
        return $this->scopeConfig->getValue('mst_nav/extra_filter/sale/label', ScopeInterface::SCOPE_STORE);
    }

    public function getOnSaleFilterPosition(): int
    {
        return (int)$this->scopeConfig->getValue('mst_nav/extra_filter/sale/position', ScopeInterface::SCOPE_STORE);
    }

    public function isStockFilterEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/extra_filter/stock/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getStockFilterLabel(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/extra_filter/stock/label', ScopeInterface::SCOPE_STORE);
    }

    public function getInStockFilterLabel(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/extra_filter/stock/label_in', ScopeInterface::SCOPE_STORE);
    }

    public function getOutOfStockFilterLabel(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/extra_filter/stock/label_out', ScopeInterface::SCOPE_STORE);
    }

    public function getStockFilterPosition(): int
    {
        return (int)$this->scopeConfig->getValue('mst_nav/extra_filter/stock/position', ScopeInterface::SCOPE_STORE);
    }

    public function isRatingFilterEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/extra_filter/rating/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getRatingFilterLabel(): string
    {
        return $this->scopeConfig->getValue('mst_nav/extra_filter/rating/label', ScopeInterface::SCOPE_STORE);
    }

    public function getRatingFilterPosition(): int
    {
        return (int)$this->scopeConfig->getValue('mst_nav/extra_filter/rating/position', ScopeInterface::SCOPE_STORE);
    }

    private function transformToMethod(string $str): string
    {
        return str_replace('_', '', ucwords($str, '_'));
    }
}
