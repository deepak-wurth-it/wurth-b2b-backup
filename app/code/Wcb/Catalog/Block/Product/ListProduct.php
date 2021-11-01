<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Catalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as LocaleDate;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var Layer
     */
    protected $_catalogLayer;

    /**
     * @var PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    const XML_PATH_PRODUCT_PER_PAGE_ON_GRID = 'catalog/frontend/grid_per_page_values';
    const XML_PATH_PRODUCT_PER_PAGE_ON_GRID_DEFAULT_VALUE = 'catalog/frontend/grid_per_page';


    /**
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context             $context,
        PostHelper                                         $postDataHelper,
        Resolver                                           $layerResolver,
        CategoryRepositoryInterface                        $categoryRepository,
        Data                                               $urlHelper,
        LocaleDate                                         $localeDate,
        \Magento\Framework\Pricing\Helper\Data             $priceHelper,
        \Magento\Catalog\Model\CategoryFactory             $categoryModelFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array                                              $data = []
    )
    {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->context = $context;
        $this->urlHelper = $urlHelper;
        $this->localeDate = $localeDate;
        $this->_priceHelper = $priceHelper;
        $this->categoryModelFactory = $categoryModelFactory;
        $this->scopeConfig = $scopeConfig;


        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }


    public function getCategoryChildrenData($id = null)
    {
        $getGridDefaultProductValuePerPage = $this->getGridDefaultProductValuePerPage() ?? 4;
        $categoryCollection = '';
        $page = ($this->context->getRequest()->getParam('p')) ? $this->context->getRequest()->getParam('p') : 1;
        $pageSize = ($this->context->getRequest()->getParam('product_list_limit')) ? $this->context->getRequest()->getParam('product_list_limit') : $getGridDefaultProductValuePerPage;
        $product_list_order = ($this->context->getRequest()->getParam('product_list_order')) ? $this->context->getRequest()->getParam('product_list_order') : 'position';
        $product_list_dir = ($this->context->getRequest()->getParam('product_list_dir')) ? $this->context->getRequest()->getParam('product_list_dir') : 'ASC';
        $pageSize = (int) $pageSize;
        if ($id) {
            $categoryCollection = $this->categoryModelFactory->create()->load($id)->getCollection();
            $categoryCollection->addAttributeToSelect('*')
                ->addFieldToFilter('parent_id', ['eq' => $id]);
            $categoryCollection->setCurPage($page);
            $categoryCollection->setPageSize($pageSize);
            $categoryCollection->addAttributeToSort($product_list_order, $product_list_dir);
        }
        return $categoryCollection;

    }

    public function getCategoryData($id = null)
    {
        $category = '';
        if ($id) {
            $category = $this->categoryModelFactory->create()->load($id);
            if ($category) {
                return $category;
            }
        }
        return $category;

    }

    public function getGridDefaultProductValuePerPage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_PER_PAGE_ON_GRID_DEFAULT_VALUE, $storeScope);
    }


}
