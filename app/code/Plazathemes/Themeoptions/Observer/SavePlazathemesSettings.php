<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Plazathemes\Themeoptions\Observer;

use Magento\Framework\Event\ObserverInterface;

class SavePlazathemesSettings implements ObserverInterface
{
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
	
	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
	
    protected $_themeCollectionFactory;
	
	
    protected $_messageManager;
    
    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
	 * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Theme\Model\ThemeFactory $themeFactory
    ) {
        $this->_messageManager = $messageManager;
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_themeFactory = $themeFactory;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {		
		$websiteId = $observer->getData("website");
		
		$storeId = $observer->getData("store");
		
		 if(!$websiteId && !$storeId) {
            $websites = $this->_storeManager->getWebsites(false, false);
            foreach ($websites as $id => $value) {
                $this->generateWebsiteCss($id);
            }
        } else {
            if($storeId) {
                $this->generateStoreCss($storeId);
            } else {
                $this->generateWebsiteCss($websiteId);
            }
        }
    }
	
	 protected function generateWebsiteCss($websiteId) {
        $website = $this->_storeManager->getWebsite($websiteId);
        foreach($website->getStoreIds() as $storeId){
            $this->generateStoreCss($storeId);
        }
    }
	
	protected function generateStoreCss($storeId)
	{
		$store = $this->_storeManager->getStore($storeId);
		
		$themes = $this->_themeFactory
			->create()
			->getCollection()->addFieldToFilter('theme_id', $this->getDesign($storeId));
			
		foreach($themes as $theme)
		$themepath = $theme->getThemePath();

		/*general*/
		$css = "";
		if($this->getConfig('general/custom',$storeId))
		{
			$css .= $this->getCss('general_bg_color','general/bg_color',$store);
			$css .= $this->getCss('general_title_color','general/title_color',$store);
			$css .= $this->getCss('general_text_color','general/text_color',$store);
			$css .= $this->getCss('general_link_color','general/link_color',$store);
			$css .= $this->getCss('general_link_hover_color','general/link_hover_color',$store);
			$css .= $this->getCss('general_price_color','general/price_color',$store);
			$css .= $this->getCss('general_old_price_color','general/old_price_color',$store);
			$css .= $this->getCss('general_border_color','general/border_color',$store);
			$css .= $this->getCss('general_product_name_color','general/product_name_color',$store);
			$css .= $this->getCss('general_new_label_color','general/new_label_color',$store);
			$css .= $this->getCss('general_sale_label_color','general/sale_label_color',$store);
		}
		
		/*header*/
		if($this->getConfig('header/custom',$storeId))
		{
			$css .= $this->getCss('header_bg_color','header/bg_color',$store);
			$css .= $this->getCss('header_link_color','header/link_color',$store);
		}
		
		
		/*footer_top*/
		if($this->getConfig('footer_top/custom',$storeId))
		{
			$css .= $this->getCss('footer_top_bg_color','footer_top/bg_color',$store);
			$css .= $this->getCss('footer_top_title_color','footer_top/title_color',$store);
			$css .= $this->getCss('footer_top_text_color','footer_top/text_color',$store);
			$css .= $this->getCss('footer_top_link_color','footer_top/link_color',$store);
		}
		
		
		/*footer_bottom*/
		if($this->getConfig('footer_bottom/custom',$storeId))
		{
			$css .= $this->getCss('footer_bottom_bg_color','footer_bottom/bg_color',$store);
			$css .= $this->getCss('footer_bottom_text_color','footer_bottom/text_color',$store);
			$css .= $this->getCss('footer_bottom_link_color','footer_bottom/link_color',$store);
		}
		
		$dirPath = BP.'/app/design/frontend/'.$themepath.'/web/css/source/';
		$filePath = $dirPath.'_options.less';
		
		try {
			if(!file_exists($dirPath)) {
				@mkdir($dirPath, 0777);
			}
			$file = @fopen($filePath,"w+");
			@flock($file, LOCK_EX);
			@fwrite($file,$css);
			@flock($file, LOCK_UN);
			@fclose($file);
		} catch (\Exception $e) {
			$this->_messageManager->addError(__($e->getMessage()));
		}
	}
    
	private function getCss($name,$value,$store)
	{
		if($this->getConfig($value,$store->getId()))
			return "@$name:#". $this->getConfig($value,$store->getId()).";\n";
		return "";
	}
	
	public function getConfig($field,$storeId)
	{
		return $this->_scopeConfig->getValue('plazathemes_design/'.$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
	}
	
	public function getDesign($storeId)
	{
		return $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
	}
}
