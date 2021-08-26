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

namespace Mirasvit\LayeredNavigation\Service;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\ConfigProvider as SeoFilterConfig;
use Mirasvit\SeoFilter\Service\RewriteService;
use Mirasvit\SeoFilter\Service\UrlService as SeoUrlService;

class SliderService
{
    const MATCH_PREFIX            = 'slider_match_prefix_';
    const SLIDER_DATA             = 'sliderdata';
    const SLIDER_URL_TEMPLATE     = self::SLIDER_REPLACE_VARIABLE . '_from-' . self::SLIDER_REPLACE_VARIABLE . '_to';
    const SLIDER_REPLACE_VARIABLE = '[attr]';

    protected static $sliderOptions;

    private          $configProvider;

    private          $rewriteService;

    private          $request;

    private          $storeId;

    private          $urlHelper;

    private          $urlBuilder;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        SeoUrlService $urlHelper,
        RewriteService $rewriteService
    ) {
        $this->request        = $request;
        $this->urlBuilder     = $urlBuilder;
        $this->urlHelper      = $urlHelper;
        $this->rewriteService = $rewriteService;
        $this->configProvider = $configProvider;
        $this->storeId        = (int)$storeManager->getStore()->getStoreId();
    }

    public function getSliderData(array $facetedData, string $requestVar, array $fromToData, string $url): array
    {
        $min = null;
        $max = null;
        foreach ($facetedData as $item) {
            if ($min === null || $min > $item['from']) {
                $min = $item['from'];
            }

            if ($max === null || $max < $item['to']) {
                $max = $item['to'];
            }
        }

        $from = ($fromToData) ? $fromToData['from'] : $min;
        $to   = ($fromToData) ? $fromToData['to'] : $max;

        $sliderData = [
            'min'        => $min,
            'max'        => $max,
            'requestVar' => $requestVar,
            'from'       => $from,
            'to'         => $to,
            'url'        => $url,
        ];


        return $sliderData;
    }

    public function getSliderUrl(FilterInterface $filter, string $template): string
    {
        if ($this->configProvider->isSeoFiltersEnabled()
            && in_array($this->request->getFullActionName(), [
                'catalog_category_view',
                'all_products_page_index_index',
                'brand_brand_view',
            ])
        ) {
            return $this->getSliderSeoFriendlyUrl($filter, $template);
        }

        $query = [$filter->getRequestVar() => $template];

        return $this->urlBuilder->getUrl('*/*/*', [
            '_current'     => true,
            '_use_rewrite' => true,
            '_query'       => $query,
        ]);
    }

    public function getParamTemplate(FilterInterface $filter): string
    {
        $requestVar = $filter->getRequestVar();

        return str_replace(
            SliderService::SLIDER_REPLACE_VARIABLE,
            $requestVar,
            SliderService::SLIDER_URL_TEMPLATE
        );
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    private function getSliderSeoFriendlyUrl(FilterInterface $filter, string $template): string
    {
        $activeFilters = $this->rewriteService->getActiveFilters();
        if (!$activeFilters || $this->isFilterCategoryOnly($activeFilters)) {
            $separator = '/';
        } else {
            $separator = SeoFilterConfig::SEPARATOR_FILTERS;
        }

        $price      = $filter->getRequestVar() . SeoFilterConfig::SEPARATOR_DECIMAL . $template;
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $suffix     = $this->urlHelper->getCategoryUrlSuffix($this->storeId);

        $rewrite        = $this->rewriteService->getAttributeRewrite($filter->getRequestVar());
        $attributeAlias = $rewrite ? $rewrite->getRewrite() : $filter->getRequestVar();

        if (isset($activeFilters[$filter->getRequestVar()])) { //delete old param from url
            $currentUrlPrepared      = strtok($currentUrl, '?');
            $currentUrlPreparedArray = explode('/', $currentUrlPrepared);
            $priceValue              = $currentUrlPreparedArray[count($currentUrlPreparedArray) - 1];
            $priceValue              = ($suffix) ? str_replace($suffix, '', $priceValue) : $priceValue;
            $priceValueArray         = explode($filter->getRequestVar(), $priceValue);
            if (isset($priceValueArray[1])) {
                $priceValue = $filter->getRequestVar() . $priceValueArray[1];
            }
            $currentUrl = str_replace($priceValue, '', $currentUrl);
        }

        if ($this->configProvider->getSeoFiltersUrlFormat() === 'attr_options') {
            $currentUrl = str_replace($suffix, '/' . $attributeAlias . '/' . $template . $suffix, $currentUrl);
            $currentUrl = str_replace('//' . $attributeAlias, '', $currentUrl);
        } elseif ($suffix && $suffix !== '/' && strpos($currentUrl, $suffix) !== false) {
            $currentUrl = str_replace($suffix, $separator . $price . $suffix, $currentUrl);
        } elseif (strpos($currentUrl, '?') !== false) {
            $currentUrl = str_replace('?', $separator . $price . '?', $currentUrl);
        } else {
            $currentUrl = rtrim($currentUrl, $separator) . $separator . $price;
        }

        $currentUrl = str_replace(
            SeoFilterConfig::SEPARATOR_FILTERS . SeoFilterConfig::SEPARATOR_FILTERS,
            SeoFilterConfig::SEPARATOR_FILTERS,
            $currentUrl
        );
        $currentUrl = str_replace('/' . SeoFilterConfig::SEPARATOR_FILTERS, '/', $currentUrl);


        return $currentUrl;
    }

    /**
     * @param array|null $activeFilters
     *
     * @return bool
     */
    private function isFilterCategoryOnly($activeFilters)
    {
        if (!is_array($activeFilters)) {
            return false;
        }
        if (count($activeFilters) == 1 && array_key_exists('cat', $activeFilters)) {
            return true;
        }

        return false;
    }
}
