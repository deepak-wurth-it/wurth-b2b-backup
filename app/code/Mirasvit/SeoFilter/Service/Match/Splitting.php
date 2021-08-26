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



namespace Mirasvit\SeoFilter\Service\Match;

use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Service\RewriteService;
use Mirasvit\SeoFilter\Service\UrlService;

class Splitting
{
    private $rewriteService;

    private $urlService;

    private $configProvider;

    private $context;

    public function __construct(
        RewriteService $rewriteService,
        UrlService $urlService,
        ConfigProvider $configProvider,
        Context $context
    ) {
        $this->rewriteService = $rewriteService;
        $this->urlService     = $urlService;
        $this->configProvider = $configProvider;
        $this->context        = $context;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param string $basePath
     *
     * @return array
     */
    public function getFiltersString(string $basePath): array
    {
        $uri = trim($this->context->getRequest()->getPathInfo(), '/');

        $suffix = $this->urlService->getCategoryUrlSuffix();
        if ($suffix && substr($uri, -strlen($suffix)) === $suffix) {
            $uri = substr($uri, 0, -strlen($suffix));
        }

        $filtersString = trim(str_replace($basePath, '', $uri), '/');

        $prefix = $this->configProvider->getPrefix();
        if ($prefix && substr($filtersString, 0, strlen($prefix)) === $prefix) {
            $filtersString = trim(substr($filtersString, strlen($prefix)), '/');
        }

        if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
            $result     = [];
            $filterInfo = explode('/', $filtersString);

            for ($i = 0; $i <= count($filterInfo) - 2; $i += 2) {
                $attributeAlias = (string)$filterInfo[$i];
                $rewrite        = $this->rewriteService->getAttributeRewriteByAlias($attributeAlias);
                $attributeCode  = $rewrite ? $rewrite->getAttributeCode() : $attributeAlias;
                foreach ($this->splitFiltersString($filterInfo[$i + 1]) as $opt) {
                    $result[$attributeCode][] = $opt;
                }
            }

            return $result;
        } else {
            $result     = [];
            $filterInfo = explode('/', $filtersString);

            foreach ($filterInfo as $part) {
                foreach ($this->splitFiltersString($part) as $opt) {
                    $result['*'][] = $opt;
                }
            }

            return $result;
        }
    }

    private function splitFiltersString(string $filtersString): array
    {
        $filterInfo = explode(ConfigProvider::SEPARATOR_FILTERS, $filtersString);
        foreach ($filterInfo as $key => $value) {
            $filterInfo[$key] = $value;
        }

        $filterInfo = array_diff($filterInfo, ['', null, false]);

        return $filterInfo;
    }
}
