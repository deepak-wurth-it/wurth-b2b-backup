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

use Magento\Catalog\Model\Layer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\LayeredNavigation\Model\Config\HorizontalBarConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\Source\FilterApplyingModeSource;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxResponseService
{
    private $resolver;

    private $resultRawFactory;

    private $urlBuilder;

    private $urlService;

    private $config;

    private $storeId;

    private $request;

    private $horizontalFiltersConfig;

    private $moduleManager;

    public function __construct(
        Layer\Resolver $resolver,
        RawFactory $resultRawFactory,
        UrlInterface $urlBuilder,
        UrlService $urlService,
        ConfigProvider $config,
        StoreManagerInterface $storeManager,
        Http $request,
        HorizontalBarConfigProvider $horizontalFiltersConfig,
        ModuleManager $moduleManager
    ) {
        $this->resolver                = $resolver;
        $this->resultRawFactory        = $resultRawFactory;
        $this->urlBuilder              = $urlBuilder;
        $this->urlService              = $urlService;
        $this->config                  = $config;
        $this->storeId                 = $storeManager->getStore()->getStoreId();
        $this->request                 = $request;
        $this->horizontalFiltersConfig = $horizontalFiltersConfig;
        $this->moduleManager           = $moduleManager;
    }

    /**
     * @param Page $page
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function getAjaxResponse(Page $page)
    {
        $mode = $this->request->getParam('mode');

        if ($this->config->isAjaxEnabled()) {
            switch ($mode) {
                case FilterApplyingModeSource::OPTION_BY_BUTTON_CLICK:
                    $data = $this->buildDataConfirmationMode($page);
                    break;

                case FilterApplyingModeSource::OPTION_INSTANTLY:
                default:
                    $data = $this->buildDataInstantMode($page);
                    break;
            }
        } else {
            $data = $this->buildDataInstantMode($page);
        }

        $data = $this->prepareAjaxData($data);

        return $this->createResponse($data);
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = ['_' => null];

        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);
        $currentUrl = $this->urlService->getPreparedUrl($currentUrl);

        return $currentUrl;
    }

    /**
     * @param string[] $data
     *
     * @return string
     */
    protected function prepareAjaxData($data)
    {
        $map = [
            '&amp;'                  => '&',
            '?isAjax=1&'             => '?',
            '?isAjax=1'              => '',
            '&isAjax=1'              => '',
            '?isAjax=true&'          => '?',
            '?isAjax=true'           => '',
            '&isAjax=true'           => '',
            '?mode=by_button_click&' => '?',
            '?mode=by_button_click'  => '',
            '&mode=by_button_click&' => '&',
            '&mode=by_button_click'  => '',
        ];

        foreach ($map as $search => $replace) {
            $data = str_replace($search, $replace, $data);
        }

        return $data;
    }

    /**
     * @param string $data
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    protected function createResponse($data)
    {
        $response = $this->resultRawFactory->create()
            ->setHeader('Content-type', 'text/plain')
            ->setContents(\Zend_Json::encode($data));

        return $response;
    }

    private function buildDataInstantMode(Page $page)
    {
        $layout              = $page->getLayout();
        $productsHtml        = $this->getProductsHtml($page);
        $productsCount       = $this->getProductsCount();
        $leftNavHtml         = $this->getBlockHtml($page, 'catalog.leftnav', 'catalogsearch.leftnav');
        $filterExpanderHtml  = $this->getBlockHtml($page, 'mst-nav.filterExpander');
        $breadcrumbHtml      = $this->getBlockHtml($page, 'breadcrumbs');
        $pageTitleHtml       = $this->getBlockHtml($page, 'page.main.title');
        $quickNavigationHtml = $this->getBlockHtml($page, 'quick_navigation.filter_list');

        $categoryViewData = '';
        $children         = $layout->getChildNames('category.view.container');
        foreach ($children as $child) {
            $categoryViewData .= $layout->renderElement($child);
        }

        $categoryViewData     = '<div class="category-view">' . $categoryViewData . '</div>';
        $horizontalNavigation = $this->getBlockHtml($page, 'm.catalog.horizontal');

        $seoTitleH1Html = $this->getSeoTitleH1Html($page);

        $data = [
            'products'         => $productsHtml,
            'products_count'   => $productsCount,
            'leftnav'          => $leftNavHtml . $filterExpanderHtml,
            'quickNavigation'  => $quickNavigationHtml,
            'breadcrumbs'      => $breadcrumbHtml,
            'pageTitle'        => $seoTitleH1Html ? $seoTitleH1Html : $pageTitleHtml,
            'categoryViewData' => $categoryViewData,
            'url'              => $this->config->isSeoFiltersEnabled() ? 'mNavigationAjax->getAjaxResult' : $this->getCurrentUrl(),
            'horizontalBar'    => '<div class="mst-nav__horizontal-bar">' . $horizontalNavigation . '</div>',
        ];

        try {
            $sidebarTag   = $layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_TAG);
            $sidebarClass = $layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_CLASS);

            if (method_exists($layout, 'renderNonCachedElement')) {
                $sidebarAdditional = $layout->renderNonCachedElement('div.sidebar.additional');
            } else {
                $sidebarAdditional = '';
            }

            $data['sidebarAdditional']         = $sidebarAdditional;
            $data['sidebarAdditionalSelector'] = $sidebarTag . '.' . str_replace(' ', '.', $sidebarClass);
        } catch (\Exception $e) {
        }

        if ($this->moduleManager->isEnabled('Lof_AjaxScroll')) {
            $data['products'] .= $layout->createBlock('Lof\AjaxScroll\Block\Init')
                ->setTemplate('Lof_AjaxScroll::init.phtml')->toHtml();
            $data['products'] .= $layout->createBlock('Lof\AjaxScroll\Block\Init')
                ->setTemplate('Lof_AjaxScroll::scripts.phtml')->toHtml();
            $data['products'] .= "<script>window.ias.nextUrl = window.ias.getNextUrl();</script>";
        }

        if ($this->moduleManager->isEnabled('Mirasvit_Scroll')) {
            $data['products'] .= $layout->createBlock('Mirasvit\Scroll\Block\Scroll')
                ->setTemplate('Mirasvit_Scroll::scroll.phtml')->toHtml();
        }

        return $data;
    }

    private function getSeoTitleH1Html(Page $page)
    {
        if (!$this->moduleManager->isEnabled('Mirasvit_SeoContent')) {
            return null;
        }

        $contentService = \Magento\Framework\App\ObjectManager::getInstance()->get('\Mirasvit\SeoContent\Service\ContentService');
        if (!$contentService) {
            return null;
        }

        $currentContent = $contentService->getCurrentContent();
        $seoTitleH1     = $currentContent->getTitle();
        if (!$seoTitleH1) {
            return null;
        }

        $titleH1Block = $this->getBlock($page, 'page.main.title');
        if (!$titleH1Block) {
            return null;
        }

        $titleH1Block->setPageTitle($seoTitleH1);
        $seoTitleH1Html = $titleH1Block->toHtml();

        return $seoTitleH1Html ? $seoTitleH1Html : null;
    }

    private function getProductsHtml(Page $page)
    {
        if (in_array($this->request->getFullActionName(), ['brand_brand_view', 'all_products_page_index_index'])) {
            $productsHtml = $this->getBlockHtml($page, 'category.products.list');
        } else {
            $productsHtml = $this->getBlockHtml($page, 'category.products', 'search.result');
        }

        return $productsHtml;
    }

    private function buildDataConfirmationMode(Page $page)
    {
        if ($this->request->getFullActionName() === 'catalogsearch_result_index') {
            $this->getProductsHtml($page);
        }

        if ($this->config->isSeoFiltersEnabled() && $this->request->getFullActionName() !== 'catalogsearch_result_index') {
            /** @var \Mirasvit\SeoFilter\Service\FriendlyUrlService $friendlyUrlService */
            $friendlyUrlService = ObjectManager::getInstance()->get(\Mirasvit\SeoFilter\Service\FriendlyUrlService::class);

            $url = $friendlyUrlService->getUrl('', '');
        } else {
            $url = $this->getCurrentUrl();
        }

        return [
            'products'         => '',
            'products_count'   => $this->getProductsCount(),
            'leftnav'          => '',
            'quickNavigation'  => '',
            'breadcrumbs'      => '',
            'pageTitle'        => '',
            'categoryViewData' => '',
            'url'              => $url,
            'horizontalBar'    => '',
        ];
    }

    private function getProductsCount()
    {
        $productCollection = $this->resolver->get()->getProductCollection();

        return $productCollection ? $productCollection->getSize() : 0;
    }

    /**
     * @param Page   $page
     * @param string $blockName
     * @param string $fallbackBlockName
     *
     * @return string
     */
    private function getBlockHtml(Page $page, $blockName, $fallbackBlockName = '')
    {
        $block = $this->getBlock($page, $blockName, $fallbackBlockName);

        return $block ? $block->toHtml() : '';
    }

    /**
     * @param Page   $page
     * @param string $blockName
     * @param string $fallbackBlockName
     *
     * @return \Magento\Framework\View\Element\BlockInterface|null
     */
    private function getBlock(Page $page, $blockName, $fallbackBlockName = '')
    {
        $layout = $page->getLayout();
        $block  = $layout->getBlock($blockName);

        if (!$block && $fallbackBlockName) {
            $block = $layout->getBlock($fallbackBlockName);
        }

        return $block ? $block : null;
    }
}
