<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\NewFeature\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class CatalogBreadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;
    protected $path = array();

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct(Context $context, Data $catalogData, array $data = [])
    {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $data);
    }

    public function getTitleSeparator($store = null)
    {
        $separator = (string) $this->_scopeConfig->getValue('catalog/seo/title_separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return ' ' . $separator . ' ';
    }

    public function getBreadcrumb()
    {
        // $this->addCrumb(
        //         'home', [
        //     'label' => __('Home'),
        //     'title' => __('Go to Home Page'),
        //     'link' => $this->getBaseUrl()
        //         ]
        // );
        foreach ((array) $this->path as $name => $breadcrumb) {
            $this->addCrumb($name, $breadcrumb);
        }
        return $this->getCrumbs();
    }

    protected function _prepareLayout()
    {
        $this->path = $this->_catalogData->getBreadcrumbPath();
        $title = [];
        foreach ((array) $this->path as $name => $breadcrumb) {
            $title[] = $breadcrumb['label'];
        }
        return $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));
        //return parent::_prepareLayout();
    }

    public function getCrumbs()
    {
        return $this->_crumbs;
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}