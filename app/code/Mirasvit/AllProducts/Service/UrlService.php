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

namespace Mirasvit\AllProducts\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface as MagentoUrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\AllProducts\Config\Config;

class UrlService
{
    const IS_CORRECT_URL = 'is_all_products_url';

    private $config;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var MagentoUrlInterface
     */
    private $urlManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        MagentoUrlInterface $urlManager,
        Config $config,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->urlManager   = $urlManager;
        $this->config       = $config;
        $this->registry     = $registry;
    }

    public function getBaseRoute(): string
    {
        return $this->config->getUrlKey();
    }

    public function match(string $pathInfo): ?DataObject
    {
        $identifier = trim($pathInfo, '/');
        $parts      = explode('/', $identifier);

        if ($parts[0] != $this->getBaseRoute()) {
            return null;
        }

        $urlKey = $parts[0];

        return new DataObject([
            'module_name'     => 'all_products_page',
            'controller_name' => 'index',
            'action_name'     => 'index',
            'route_name'      => $urlKey,
            'params'          => [],
        ]);
    }

    public function getClearUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . $this->config->getUrlKey();
    }
}
