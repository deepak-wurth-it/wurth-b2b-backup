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

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SeoFilter\Model\Context;

class UrlService
{
    /**
     * Cache for category rewrite suffix
     * @var array
     */
    private $categoryUrlSuffix = [];

    private $scopeConfig;

    private $storeManager;

    private $registry;

    private $context;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->registry     = $registry;
        $this->context      = $context;
    }


    public function getCategoryUrlSuffix(int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->categoryUrlSuffix[$storeId])) {
            $this->categoryUrlSuffix[$storeId] = (string)$this->scopeConfig->getValue(
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->categoryUrlSuffix[$storeId];
    }

    public function getBrandUrlSuffix(int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return (string)$this->scopeConfig->getValue('brand/general/url_suffix', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function trimCategorySuffix(string $url): string
    {
        $suffix = $this->getCategoryUrlSuffix();

        if ($suffix && $suffix !== '/') {
            $url = str_replace($suffix, '', $url);
        }

        return $url;
    }

    /**
     * Return catalog current category object
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    public function getQueryParams(string $url = ''): string
    {
        $currentUrl = $this->context->getUrlBuilder()->getCurrentUrl();

        if ($url) {
            return strtok($currentUrl, '?') . strstr($url, '?', false);
        }

        return strstr($currentUrl, '?', false);
    }

    public function getGetParams(): array
    {
        $currentUrl = (string)$this->context->getUrlBuilder()->getCurrentUrl();

        if (parse_url($currentUrl, PHP_URL_QUERY) === null) {
            return [];
        }

        $params = [];
        parse_str((string)parse_url($currentUrl, PHP_URL_QUERY), $params);

        return $params;
    }

    public function addUrlParams(string $url): string
    {
        return $this->mergeGetParams(
            $url,
            (string)$this->context->getUrlBuilder()->getCurrentUrl()
        );
    }

    /** $urlA + GET($urlA) + GET($urlB) */
    private function mergeGetParams(string $urlA, string $urlB): string
    {
        $aParams = [];
        parse_str((string)parse_url($urlA, PHP_URL_QUERY), $aParams);

        $bParams = [];
        parse_str((string)parse_url($urlB, PHP_URL_QUERY), $bParams);

        foreach ($aParams as $key => $value) {
            $bParams[$key] = $value;
        }

        $query = '';

        if (count($bParams)) {
            $query = '?' . http_build_query($bParams);
        }

        return strtok($urlA, '?') . $query;
    }
}
