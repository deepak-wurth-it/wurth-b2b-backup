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

namespace Mirasvit\Brand\Plugin\Frontend\Magento\Theme\Block\Html\Topmenu;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Model\Config\Source\BrandsLinkPositionOptions;
use Mirasvit\Brand\Service\BrandUrlService;

class FirstBrandLinkPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $brandUrlService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url,
        Config $config,
        BrandUrlService $brandUrlService,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->url             = $url;
        $this->config          = $config;
        $this->brandUrlService = $brandUrlService;
        $this->storeManager    = $storeManager;
    }

    /**
     * @param Topmenu $subject
     * @param mixed   ...$args
     */
    public function beforeGetHtml(Topmenu $subject, ...$args)
    {
        if (!$this->isBrandLinkEnabled()) {
            return;
        }

        $node = new Node(
            $this->_getNodeAsArray(),
            'id',
            $subject->getMenu()->getTree(),
            $subject->getMenu()
        );
        $subject->getMenu()->addChild($node);
    }

    /**
     * @return array
     */
    protected function _getNodeAsArray()
    {
        $url = $this->brandUrlService->getBaseBrandUrl();

        return [
            'name'       => $this->config->getGeneralConfig()->getBrandLinkLabel() ? : __('Brands'),
            'id'         => 'm___all_brands_page_link',
            'url'        => $url,
            'has_active' => false,
            'is_active'  => $url === $this->url->getCurrentUrl(),
        ];
    }

    /**
     * @return int
     */
    protected function getBrandLinkPosition()
    {
        return BrandsLinkPositionOptions::TOP_MENU_FIRST;
    }

    /**
     * @return bool
     */
    protected function isBrandLinkEnabled()
    {
        return $this->getBrandLinkPosition() == $this->config->getGeneralConfig()->getBrandLinkPosition();
    }
}
