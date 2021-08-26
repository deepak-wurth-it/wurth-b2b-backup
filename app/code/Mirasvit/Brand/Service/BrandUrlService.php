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

namespace Mirasvit\Brand\Service;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandRepository;

class BrandUrlService
{
    const LONG_URL  = 0;
    const SHORT_URL = 1;


    private $brandRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FilterManager
     */
    private $filter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        BrandRepository $brandRepository,
        Config $config,
        FilterManager $filter
    ) {
        $this->brandRepository = $brandRepository;
        $this->config          = $config;
        $this->filter          = $filter;
        $this->storeManager    = $storeManager;
    }

    public function getBaseBrandUrl(?int $storeId = 0): string
    {
        if ($storeId) {
            return $this->storeManager->getStore($storeId)->getBaseUrl() . $this->getBaseRoute(true, $storeId);
        }

        return $this->storeManager->getStore()->getBaseUrl() . $this->getBaseRoute(true);
    }

    /**
     * @param mixed    $brand
     * @param int|null $storeId
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandUrl($brand, ?int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();
        }

        $urlKey = $brand->getUrlKey();

        $formatBrandUrl = $this->config->getGeneralConfig()->getFormatBrandUrl();

        if ($formatBrandUrl === self::SHORT_URL) {
            $brandUrl = $urlKey;
        } else {
            $brandUrl = $this->getBaseRoute(false, $storeId) . '/' . $urlKey;
        }

        $brandUrl = $this->storeManager->getStore($storeId)->getBaseUrl() . $brandUrl;

        return $brandUrl . $this->config->getGeneralConfig()->getUrlSuffix();
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    public function match(string $pathInfo): ?DataObject
    {
        $identifier   = trim($pathInfo, '/');
        $parts        = explode('/', $identifier);
        $brandUrlKeys = $this->getAvailableBrandUrlKeys();

        if ($parts[0] !== $this->getBaseRoute() && !in_array($parts[0], $brandUrlKeys, true)) {
            return null;
        }

        $urlType   = $this->config->getGeneralConfig()->getFormatBrandUrl();
        $urlKey    = $parts[0];
        $baseRoute = $this->getBaseRoute(true);

        if ($urlType === self::SHORT_URL && $urlKey != $baseRoute && in_array($urlKey, $brandUrlKeys, true)) {
            $optionId = array_search($urlKey, $brandUrlKeys, true);

            return new DataObject([
                'module_name'     => 'brand',
                'controller_name' => 'brand',
                'action_name'     => 'view',
                'route_name'      => $brandUrlKeys[$optionId],
                'params'          => [BrandPageInterface::ATTRIBUTE_OPTION_ID => $optionId],
            ]);
        } elseif (isset($parts[1]) && in_array($parts[1], $brandUrlKeys, true)) {
            $optionId = array_search($parts[1], $brandUrlKeys, true);

            return new DataObject([
                'module_name'     => 'brand',
                'controller_name' => 'brand',
                'action_name'     => 'view',
                'route_name'      => $brandUrlKeys[$optionId],
                'params'          => [BrandPageInterface::ATTRIBUTE_OPTION_ID => $optionId],
            ]);
        } elseif ($urlKey === $baseRoute) {
            return new DataObject([
                'module_name'     => 'brand',
                'controller_name' => 'brand',
                'action_name'     => 'index',
                'route_name'      => $urlKey,
                'params'          => [],
            ]);
        }

        return null;
    }

    private function getBaseRoute(bool $withSuffix = false, ?int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();
        }

        $baseRoute = $this->config->getGeneralConfig()->getAllBrandUrl($storeId);

        if ($withSuffix) {
            $baseRoute .= $this->config->getGeneralConfig()->getUrlSuffix();
        }

        return $baseRoute;
    }

    /**  @return string[] */
    private function getAvailableBrandUrlKeys(): array
    {
        $urlKeys = [$this->getBaseRoute(true)];

        $brandPages = $this->brandRepository->getList();

        foreach ($brandPages as $brand) {
            $urlKeys[$brand->getId()] = $brand->getUrlKey() . $this->config->getGeneralConfig()->getUrlSuffix();
        }

        return $urlKeys;
    }
}
