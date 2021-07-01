<?php 
namespace Plazathemes\Newsletterpopup\Block;
class Newsletterpopup extends  \Magento\Newsletter\Block\Subscribe
{
    protected $httpContext;	

	public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\App\Http\Context $httpContext,
		array $data = []
	) {
		$this->httpContext = $httpContext;
		parent::__construct(
			$context,
			$data
		);
		$this->_isScopePrivate = true;
	}
	
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
	
		public function _prepareLayout()
	{ 

		return parent::_prepareLayout();
	}
	
	public function getConfig($value=''){

	   $config =  $this->_scopeConfig->getValue('newsletterpopup/popup_group/'.$value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	   return $config; 
	 
	}
	
	public function getMediaUrl() {
		return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	

}