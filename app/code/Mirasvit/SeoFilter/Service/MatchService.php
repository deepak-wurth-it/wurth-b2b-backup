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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Service;

use Magento\Framework\ObjectManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Repository\RewriteRepository;

class MatchService
{
    const DECIMAL_FILTERS = 'decimalFilters';
    const STATIC_FILTERS  = 'staticFilters';

    private $splitting;

    private $rewriteRepository;

    private $urlRewrite;

    private $urlService;

    private $context;

    private $configProvider;

    private $objectManager;

    public function __construct(
        Match\Splitting $splitting,
        RewriteRepository $rewriteRepository,
        UrlRewriteCollectionFactory $urlRewrite,
        UrlService $urlService,
        ConfigProvider $configProvider,
        ObjectManagerInterface $objectManager,
        Context $context
    ) {
        $this->splitting         = $splitting;
        $this->rewriteRepository = $rewriteRepository;
        $this->urlRewrite        = $urlRewrite;
        $this->urlService        = $urlService;
        $this->configProvider    = $configProvider;
        $this->objectManager     = $objectManager;
        $this->context           = $context;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getParams(): ?array
    {
        if ($this->isNativeRewrite()) {
            return null;
        }

        $categoryId       = 0;
        $isBrandPage      = false;
        $isAllProductPage = false;

        $currentUrl = $this->context->getUrlBuilder()->getCurrentUrl();
        $urlPath    = parse_url($currentUrl, PHP_URL_PATH);

        $baseUrlPathAll      = 'all';
        $baseUrlPathBrand    = $this->getBaseBrandUrlPath();
        $baseUrlPathCategory = '';

        if (preg_match('~^/' . $baseUrlPathAll . '/\S+~', $urlPath)) {
            $isAllProductPage = true;
        } elseif (preg_match('~^/' . $baseUrlPathBrand . '/\S+~', $urlPath)) {
            $isBrandPage = true;
        } else {
            $categoryId = $this->getCategoryId();
        }
        if (!$categoryId && !$isBrandPage && !$isAllProductPage) {
            return null;
        }

        if ($categoryId) {
            $baseUrlPathCategory = $this->getCategoryBaseUrlPath($categoryId);
        }

        if ($isBrandPage) {
            $baseUrlPath = $baseUrlPathBrand;
        } elseif ($isAllProductPage) {
            $baseUrlPath = $baseUrlPathAll;
        } else {
            $baseUrlPath = $baseUrlPathCategory;
        }
        $filterData = $this->splitting->getFiltersString($baseUrlPath);

        $staticFilters  = [];
        $decimalFilters = [];

        $decimalFilters = $this->handleDecimalFilters($filterData, $decimalFilters);

        $staticFilters = $this->handleStockFilters($filterData, $staticFilters);
        $staticFilters = $this->handleRatingFilters($filterData, $staticFilters);
        $staticFilters = $this->handleSaleFilters($filterData, $staticFilters);
        $staticFilters = $this->handleNewFilters($filterData, $staticFilters);
        $staticFilters = $this->handleAttributeFilters($filterData, $staticFilters);

        $params = [];

        foreach ($decimalFilters as $attr => $values) {
            $params[$attr] = implode(ConfigProvider::SEPARATOR_FILTER_VALUES, $values);
        }

        foreach ($staticFilters as $attr => $values) {
            $params[$attr] = implode(ConfigProvider::SEPARATOR_FILTER_VALUES, $values);
        }

        $match = count($filterData) == 0;

        $result = [
            'is_all_pages'  => $isAllProductPage,
            'is_brand_page' => $isBrandPage,
            'category_id'   => $categoryId,
            'params'        => $params,
            'match'         => $match,
        ];

        return $result;
    }

    private function getBaseBrandUrlPath(): string
    {
        $brandPath = 'brand';

        $urlPath = parse_url($this->context->getUrlBuilder()->getCurrentUrl(), PHP_URL_PATH);

        if (!class_exists('Mirasvit\Brand\Model\Config\GeneralConfig')) {
            return $brandPath;
        }

        /** @var \Mirasvit\Brand\Model\Config\GeneralConfig|object $brandConfig */
        $brandConfig = $this->objectManager->get('Mirasvit\Brand\Model\Config\GeneralConfig');

        $brandPath = $brandConfig->getAllBrandUrl();

        /** @var \Mirasvit\Brand\Repository\BrandRepository|object $brandRepository */
        $brandRepository = $this->objectManager->get('Mirasvit\Brand\Repository\BrandRepository');
        foreach ($brandRepository->getList() as $brand) {
            if (preg_match('/\/' . $brand->getUrlKey() . '\/\S+/', $urlPath)) {
                if ($brandConfig->getFormatBrandUrl() == 1) {
                    $brandPath = $brand->getUrlKey();
                    break;
                } else {
                    $brandPath = $brandConfig->getAllBrandUrl() . '/' . $brand->getUrlKey();
                    break;
                }
            }
        }

        return $brandPath;
    }

    private function getCategoryId(): ?int
    {
        $requestPath = trim($this->context->getRequest()->getPathInfo(), '/');

        while ($requestPath) {
            if (strrpos($requestPath, '/') === false) {
                return null;
            }

            $requestPath = substr($requestPath, 0, strrpos($requestPath, '/'));

            if ($separator = $this->configProvider->getPrefix()) {
                $requestPath = str_ireplace('/' . $separator, '', $requestPath);
            }

            if ($suffix = $this->urlService->getCategoryUrlSuffix()) {
                $requestPath = $requestPath . $suffix;
            }

            /** @var \Magento\UrlRewrite\Model\UrlRewrite $item */
            $item = $this->urlRewrite->create()
                ->addFieldToFilter('entity_type', 'category')
                ->addFieldToFilter('redirect_type', 0)
                ->addFieldToFilter('store_id', $this->context->getStoreId())
                ->addFieldToFilter('request_path', $requestPath)
                ->getFirstItem();

            $categoryId = (int)$item->getEntityId();

            if ($categoryId) {
                return $categoryId;
            }
        }

        return null;
    }

    private function getCategoryBaseUrlPath(int $categoryId): string
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $item */
        $item = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->addFieldToFilter('entity_id', $categoryId)
            ->getFirstItem();

        $url = (string)$item->getData('request_path');
        if ($suffix = $this->urlService->getCategoryUrlSuffix()) {
            $url = str_replace($suffix, '', $url);
        }

        return $url;
    }

    private function isNativeRewrite(): bool
    {
        $requestString = trim($this->context->getRequest()->getPathInfo(), '/');

        $requestPathRewrite = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->addFieldToFilter('request_path', $requestString);

        return $requestPathRewrite->getSize() > 0;
    }

    private function handleStockFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::LABEL_STOCK_IN,
            2 => ConfigProvider::LABEL_STOCK_OUT,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_STOCK, $options, $filterData, $staticFilters);
    }

    private function handleRatingFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::LABEL_RATING_1,
            2 => ConfigProvider::LABEL_RATING_2,
            3 => ConfigProvider::LABEL_RATING_3,
            4 => ConfigProvider::LABEL_RATING_4,
            5 => ConfigProvider::LABEL_RATING_5,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_RATING, $options, $filterData, $staticFilters);
    }

    private function handleSaleFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::FILTER_SALE,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_SALE, $options, $filterData, $staticFilters);
    }

    private function handleNewFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::FILTER_NEW,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_NEW, $options, $filterData, $staticFilters);
    }

    private function handleAttributeFilters(array &$filterData, array $staticFilters): array
    {
        foreach ($filterData as $attrCode => $filterValues) {
            $rewriteCollection = $this->rewriteRepository->getCollection()
                ->addFieldToFilter(RewriteInterface::REWRITE, ['in' => $filterValues])
                ->addFieldToFilter(RewriteInterface::STORE_ID, $this->context->getStoreId());

            if ($attrCode != '*') {
                $rewriteCollection->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attrCode);
            }

            /** @var RewriteInterface $rewrite */
            foreach ($rewriteCollection as $rewrite) {
                $rewriteAttributeCode = $rewrite->getAttributeCode();
                $optionId = $rewrite->getOption();

                $staticFilters[$rewriteAttributeCode][] = $optionId;
            }

            unset($filterData[$attrCode]);
        }

        return $staticFilters;
    }

    private function handleDecimalFilters(array &$filterData, array $decimalFilters): array
    {
        foreach ($filterData as $attrCode => $filterValues) {
            if ($attrCode != '*') {
                if ($this->context->isDecimalAttribute($attrCode)) {
                    $option = implode(ConfigProvider::SEPARATOR_FILTERS, $filterValues);

                    $decimalFilters[$attrCode][] = $option;

                    unset($filterData[$attrCode]);
                }
            } else {
                foreach ($filterValues as $filterValue) {
                    if (strpos($filterValue, ConfigProvider::SEPARATOR_DECIMAL) !== false) {
                        $exploded = explode(ConfigProvider::SEPARATOR_DECIMAL, $filterValue);
                        $attrCode = $exploded[0];
                        unset($exploded[0]);

                        $option = implode(ConfigProvider::SEPARATOR_FILTERS, $exploded);

                        $decimalFilters[$attrCode][] = $option;

                        unset($filterData[$attrCode]);
                    }
                }
            }
        }

        return $decimalFilters;
    }

    private function processBuiltInFilters(string $attrCode, array $options, array &$filterData, array $staticFilters): array
    {
        foreach ($options as $key => $label) {
            foreach ($filterData as $fKey => $value) {
                if ($value == $label) {
                    $staticFilters[$attrCode][] = $key;

                    unset($filterData[$fKey]);
                }
            }
        }

        return $staticFilters;
    }
}
