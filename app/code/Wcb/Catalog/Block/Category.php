<?php
namespace Wcb\Catalog\Block;
use Plazathemes\Hozmegamenu\Block\Menu;

class Category extends \Magento\Framework\View\Element\Template
{
    protected $_categoryFactory;
 
    protected $_storeManager;
 
    protected $_categoryNameFactory;
    protected $_menu;
 
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryNameFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        Menu $_menu,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_categoryNameFactory = $categoryNameFactory;
        $this->_categoryFactory = $collecionFactory;
        $this->_storeManager = $storeManager;
        $this->_menu = $_menu;
        parent::__construct($context, $data);
    }
 
    public function getEnableCategory()
    {
        $category = $this->_menu->getCategoryLevel2();
        //$category = $this->_categoryFactory->create()->setStore($this->_storeManager->getStore());
        return $category;
    }
 
    public function getCategoryName($categoryId)
    {
        $category = $this->_categoryNameFactory->create()->load($categoryId)->setStore($this->_storeManager->getStore());
        return $category;
    }
}