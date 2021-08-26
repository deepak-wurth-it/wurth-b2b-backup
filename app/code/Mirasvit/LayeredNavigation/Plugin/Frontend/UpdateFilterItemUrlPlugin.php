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

namespace Mirasvit\LayeredNavigation\Plugin\Frontend;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager as PagerBlock;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Service\FilterService;

/**
 * Update urls (multi-select)
 * @see \Magento\Catalog\Model\Layer\Filter\Item::getUrl()
 * @see \Magento\Catalog\Model\Layer\Filter\Item::getRemoveUrl()
 */
class UpdateFilterItemUrlPlugin
{
    const DELIMITER = ',';

    private $configProvider;

    private $filterService;

    private $pagerBlock;

    private $urlManager;

    public function __construct(
        ConfigProvider $configProvider,
        FilterService $filterService,
        UrlInterface $urlManager,
        PagerBlock $pagerBlock
    ) {
        $this->configProvider = $configProvider;
        $this->filterService  = $filterService;
        $this->urlManager     = $urlManager;
        $this->pagerBlock     = $pagerBlock;
    }

    public function afterGetUrl(Item $item, string $url): string
    {
        if (!$this->configProvider->isMultiselectEnabled()) {
            return $url;
        }

        $itemValue     = $item->getData('value');
        $itemFilter    = $item->getFilter();
        $attributeCode = $itemFilter->getRequestVar();

        $params = $this->getFilterParams();

        $params[$attributeCode][$itemValue] = $itemValue;

        return $this->getUrl($params);
    }

    public function afterGetRemoveUrl(Item $item, string $url): string
    {
        if (!$this->configProvider->isMultiselectEnabled()) {
            return $url;
        }

        $itemValue = $item->getData('value');

        if (is_array($itemValue) && count($itemValue) == 1) {
            $itemValue = preg_split('/\,|\;|\-/', (string)$itemValue[0]);
        }

        $itemValues    = is_array($itemValue) ? $itemValue : preg_split('/\,|\;|\-/', (string)$itemValue);
        $attributeCode = $item->getFilter()->getRequestVar();
        $params        = $this->getFilterParams();

        if (!isset($params[$attributeCode])) {
            return $url;
        }

        foreach ($itemValues as $value) {
            $value = (string)$value;

            unset($params[$attributeCode][$value]);

            foreach ($params[$attributeCode] as $key => $attributeOption) {
                $key = (string)$key;

                if (strripos($key, $value) !== false) {
                    unset($params[$attributeCode][$key]);
                    break;
                }
            }
        }

        return $this->getUrl($params);
    }

    private function getFilterParams(): array
    {
        $activeFilters = $this->filterService->getActiveFilters();

        $result = [];

        foreach ($activeFilters as $filter) {
            $value = $filter->getData('value');

            $values = is_array($value) ? $value : explode(self::DELIMITER, (string)$value);

            foreach ($values as $val) {
                $result[$filter->getFilter()->getRequestVar()][$val] = $val;
            }
        }

        return $result;
    }

    private function getUrl(array $filterParams): string
    {
        foreach ($filterParams as $attrCode => $values) {
            if (count($values)) {
                $filterParams[$attrCode] = implode(self::DELIMITER, $values);
            } else {
                $filterParams[$attrCode] = null;
            }
        }

        $filterParams[$this->pagerBlock->getPageVarName()] = null;

        $url = $this->urlManager->getUrl('*/*/*', [
            '_current'     => true,
            '_use_rewrite' => true,
            '_query'       => $filterParams,
        ]);

        return $url;
    }
}
