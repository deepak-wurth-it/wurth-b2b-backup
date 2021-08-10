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


namespace Mirasvit\LayeredNavigation\Service\Seo;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Mirasvit\SeoNavigation\Model\CanonicalInterface;

class CanonicalProvider implements CanonicalInterface
{
    private $href = null;

    private $config;

    private $categoryRepository;

    private $url;

    public function __construct(
        UrlInterface $url,
        CategoryRepositoryInterface $categoryRepository,
        SeoConfigProvider $config
    ) {
        $this->config             = $config;
        $this->categoryRepository = $categoryRepository;
        $this->url                = $url;
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @inheritdoc
     */
    public function getHref(RequestInterface $request)
    {
        if ($this->href === null) {
            switch ($this->config->getCanonical()) {
                case CanonicalInterface::CURRENT_URL:
                    $this->href = $request->getUriString();
                    break;
                case CanonicalInterface::WITHOUT_FILTERS:
                    $this->href = $this->getBaseUrl($request);
                    break;
                case CanonicalInterface::WITHOUT_PARAMS:
                    $this->href = rtrim($request->getDistroBaseUrl(), '/') . $request->getOriginalPathInfo();
                    break;
            }
        }

        return $this->href;
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @return string
     */
    private function getBaseUrl(RequestInterface $request)
    {
        switch ($request->getFullActionName()) {
            case 'catalog_category_view':
                return $this->categoryRepository->get($request->getParam('id'))->getUrl();
                break;
            //            case 'all_products_page_index_index':
            //                return $this->url->getUrl($this->allProductsUrl->getBaseRoute());
            //                break;
            //            case 'brand_brand_view':
            //                $brandId = $request->getParam('attribute_option_id');
            //
            //                return $this->url->getUrl($this->brandRepository->get($brandId)->getUrl());
            //                break;
        }

        return $request->getUriString();
    }
}
