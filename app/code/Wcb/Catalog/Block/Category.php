<?php
namespace Wcb\Catalog\Block;

class Category extends \Magento\Framework\View\Element\Template
{
    protected $_categoryFactory;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->_collecionFactory = $collecionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getEnableCategory()
    {
        $collection = $this->_categoryFactory->create()->getCollection()
                           ->addAttributeToSelect('*')
                           -> addAttributeToFilter('level', 2)
                           -> addAttributeToFilter('is_active',1)
                           -> addAttributeToFilter('include_in_menu',1);
        return $collection ;
    }

    public function getCategoryName($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId)->setStore($this->_storeManager->getStore());
        return $category;
    }
}
