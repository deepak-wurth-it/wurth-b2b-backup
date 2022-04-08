<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Catalog\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\App\ObjectManager;

/**
 * Product list toolbar
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{

    const XML_PATH_PRODUCT_PER_PAGE_ON_GRID = 'catalog/frontend/grid_per_page_values';
    const XML_PATH_PRODUCT_PER_PAGE_ON_GRID_DEFAULT_VALUE = 'catalog/frontend/grid_per_page';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context   $context,
        \Magento\Catalog\Model\Session                     $catalogSession,
        \Magento\Catalog\Model\Config                      $catalogConfig,
        ToolbarModel                                       $toolbarModel,
        \Magento\Framework\Url\EncoderInterface            $urlEncoder,
        ProductList                                        $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper          $postDataHelper,
        array                                              $data = [],
        ToolbarMemorizer                                   $toolbarMemorizer = null,
        \Magento\Framework\App\Http\Context                $httpContext = null,
        \Magento\Framework\Data\Form\FormKey               $formKey = null,
        \Magento\Framework\Registry                        $registry,
        \Magento\Catalog\Model\CategoryFactory             $categoryModelFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig


    )
    {
        $this->_catalogSession = $catalogSession;
        $this->_catalogConfig = $catalogConfig;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_productListHelper = $productListHelper;
        $this->_postDataHelper = $postDataHelper;
        $this->registry = $registry;
        $this->context = $context;
        $this->scopeConfig = $scopeConfig;

        $this->categoryModelFactory = $categoryModelFactory;

        parent::__construct($context,
            $catalogSession,
            $catalogConfig,
            $toolbarModel,
            $urlEncoder,
            $productListHelper,
            $postDataHelper,
            $data
        );
    }


    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {   $categoryId = '';
        $getGridDefaultProductValuePerPage = $this->getGridDefaultProductValuePerPage() ?? 4;
        $page = ($this->context->getRequest()->getParam('p')) ? $this->context->getRequest()->getParam('p') : 1;
        $pageSize = ($this->context->getRequest()->getParam('product_list_limit')) ? $this->context->getRequest()->getParam('product_list_limit') : $getGridDefaultProductValuePerPage;

        if($this->registry->registry('current_category')){
           $categoryId = $this->registry->registry('current_category')->getId();
        }
        $categoryCollection = $this->categoryModelFactory->create()->load($categoryId)->getCollection();
        $categoryCollection->addAttributeToSelect('*')
            ->addFieldToFilter('parent_id', ['eq' => $categoryId]);
        $categoryCollection->setCurPage($page);
        $categoryCollection->setPageSize($pageSize);

        $this->_collection = $categoryCollection;
        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }

        // switch between sort order options
        if ($this->getCurrentOrder()) {
            // create custom query for created_at option
            switch ($this->getCurrentOrder()) {
                case 'created_at':
                    if ($this->getCurrentDirection() == 'desc') {
                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at DESC');
                    } elseif ($this->getCurrentDirection() == 'asc') {
                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at ASC');
                    }
                    break;
                default:
                    $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
                    break;
            }
        }
        return $this;
    }

    public function getGridDefaultProductValuePerPage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_PER_PAGE_ON_GRID_DEFAULT_VALUE, $storeScope);

    }

}
