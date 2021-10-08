<?php

namespace Wcb\Catalog\Block\Category;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;

class View extends \Magento\Catalog\Block\Category\View
{
    protected $_urlInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Catalog\Model\CategoryFactory $categoryModelFactory,
        \Magento\Catalog\Helper\Output $outPutHelper,
        array $data = []
    ) {
        $this->_urlInterface = $urlInterface;
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->outPutHelper = $outPutHelper;
        $this->categoryModelFactory = $categoryModelFactory;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }


    public function getCategoryChildrenData($id = null){
        $subcategories = '';
        if($id) {
            $category = $this->categoryModelFactory->create()->load($id);
            if($category->getChildrenCategories()){
                $subcategories = $category->getChildrenCategories();
            }
        }
        return $subcategories;

    }

    public function getCategoryData($id = null){
        $category = '';
        if($id) {
            $category = $this->categoryModelFactory->create()->load($id);
            if($category){
               return $category;
            }
        }
        return $category;

    }

}
