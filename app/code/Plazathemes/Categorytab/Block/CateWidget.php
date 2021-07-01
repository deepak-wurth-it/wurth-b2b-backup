<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Plazathemes\Categorytab\Block;

/**
 * Catalog Products List widget block
 * Class ProductsList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CateWidget extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Name of request parameter for page number value
     */
    const PAGE_VAR_NAME = 'np';

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $pager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;
	
	protected $_categoryFactory;
	protected $productFactory;
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $rule;
        $this->conditionsHelper = $conditionsHelper;
		$this->_categoryFactory = $categoryFactory;
        parent::__construct(
            $context,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {  
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG,
        ], ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
  
        return [
            'CATEGORY_TAB_PRODUCTS_LIST_WIDGET'.$this->getIdentify(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            intval($this->getRequest()->getParam(self::PAGE_VAR_NAME, 1)),
            $this->getProductsPerPage()
  
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
	
	protected function _getDefaultStoreId(){
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

	public function _beforeToHtml123() {
		
		  
		$categoryId = 8;
		$cate_product = $this->getProductCate($categoryId); 
	
			

		   // $collection = $this->_categoryFactory->create()->getCollection();

            // $collection->addAttributeToSelect(
                // 'name'
            // )->addAttributeToSelect(
                // 'is_active'
            // )->setProductStoreId(
                // $storeId
            // )->setStoreId(
                // $storeId
            // );

	}
	
	public function getCategory($id) {
		return 	$_category =  $this->_categoryFactory->create()->load($id);
	}
	
	public function getConfig($value=''){

	   $config =  $this->_scopeConfig->getValue('categorytab/new_status/'.$value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	   return $config; 
	 
	}
	
	public function getProductCate($id = NULL) {
       
		$storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
		$_category =  $this->_categoryFactory->create()->load($id);

        $json_products = array();
        //load the category's products as a collection
		 $_productCollection = $this->productCollectionFactory->create()
              ->addAttributeToSelect('*')
               ->addCategoryFilter($_category);
			 $qty = $this->getConfig('qty');
			 if($qty<1) $qty = 8;
			 $_productCollection ->setPageSize($qty);
		return $_productCollection;
		
    }
	
	function getThumbUrl($thumb=null) {
		
		return   $this->_storeManager->getStore()->getBaseUrl(
					\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . 'catalog/category/' . $thumb;
	}
	

	function getThumbHoverUrl($thumb=null) {
		
		return   $this->_storeManager->getStore()->getBaseUrl(
					\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . 'catalog/category/' . $thumb;
	}
	function getThumbHover2Url($thumb=null) {
		
		return   $this->_storeManager->getStore()->getBaseUrl(
					\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . 'catalog/category/' . $thumb;
	}

   
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Product::CACHE_TAG];
    }

	
    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }
	public function getIdentify()
    {
        return $this->getData('identify');
    }
	 public function getCategoryIds()
    {
        return $this->getData('category_id');
    }
	public function getItemsDefault()
    {
        return $this->getData('items_default');
    }
	public function getItemsDesktop()
    {
        return $this->getData('items_desktop');
    }
	public function getItemsDesktopSmall()
    {
        return $this->getData('items_desktop_small');
    }
	public function getItemsTablet()
    {
        return $this->getData('items_tablet');
    }
	public function getItemsMobile()
    {
        return $this->getData('items_mobile');
    }
	public function getShowAddtocart()
    {
        return $this->getData('show_addtocart');
    }
	public function getShowWishlist()
    {
        return $this->getData('show_wishlist');
    }
	public function getShowCompare()
    {
        return $this->getData('show_compare');
    }
	public function getProductsOnRow()
    {
        return $this->getData('row_show');
    }
	
	function getcattabimage($thumb=null) {
		
		return   $this->_storeManager->getStore()->getBaseUrl(
					\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . 'catalog/category/' . $thumb;
	}
	
	function getCategorytaImage($cate_id)
	{
		$cate = $this->_categoryFactory->create()->load($cate_id);;
		$image_cate = $this->getcattabimage($cate->getCategorytabImage());
		return $image_cate;
	}
}
