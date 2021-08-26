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
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Core\Service\SerializeService;

class HorizontalBarConfigProvider
{
    const STATE_BLOCK_NAME            = 'catalog.navigation.state';
    const STATE_SEARCH_BLOCK_NAME     = 'catalogsearch.navigation.state';
    const STATE_HORIZONTAL_BLOCK_NAME = 'm.catalog.navigation.horizontal.state';

    const POSITION_SIDEBAR    = 'sidebar';
    const POSITION_HORIZONTAL = 'horizontal';
    const POSITION_BOTH       = 'both';

    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isDisplayInHorizontalBar(string $attributeCode): bool
    {
        $filters = $this->getFilters();

        foreach (['*', $attributeCode] as $filter) {
            if (isset($filters[$filter])
                && in_array($filters[$filter], [self::POSITION_HORIZONTAL, self::POSITION_BOTH])) {
                return true;
            }
        }

        return false;
    }

    public function isDisplayInSideBar(string $attributeCode): bool
    {
        $filters = $this->getFilters();

        foreach (['*', $attributeCode] as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] === self::POSITION_HORIZONTAL) {
                return false;
            }
        }

        return true;
    }

    public function getHideHorizontalFiltersValue(): int
    {
        return (int)$this->scopeConfig->getValue(
            'mst_nav/horizontal_bar/horizontal_filters_hide',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getFilters(): array
    {
        $filters = $this->scopeConfig->getValue(
            'mst_nav/horizontal_bar/filters',
            ScopeInterface::SCOPE_STORE
        );

        $filters = SerializeService::decode($filters);

        if (!$filters) {
            return [];
        }

        $result = [];

        foreach ($filters as $item) {
            $result[$item['attribute_code']] = $item['position'];
        }

        return $result;
    }
}
