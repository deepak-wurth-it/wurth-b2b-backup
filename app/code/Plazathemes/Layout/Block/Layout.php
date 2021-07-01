<?php
namespace Plazathemes\Layout\Block;
class Layout extends \Magento\Framework\View\Element\Template 
{
    
	public $_coreRegistry;
    private $_lessc; 
	protected $localeResolver;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
		$this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }
	
	
	public function renderFileLessToCss() {
		
		$file_in = '';
		$file_out = '';
		// $parser = new Less_Parser();
		// $parser->parse( '@color: #4D926F; #header { color: @color; } h2 { color: @color; }' );
		// $css = $parser->getCss();
		$url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK); 
		$static_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
		
		$design =   $this->_design->getDesignTheme(); 
		$files = array(
				'style'
		);
	
		$web_css=  BP.'/app/design/'.$design->getFullPath().'/web/css/'; 	
		$locale = $this->_localeResolver->getLocale();
		$static_css = BP.'/pub/static/'.$design->getFullPath().'/'.$locale.'/css/';
		foreach($files as $file) {
			$file_in = $web_css.$file.'.less';
			$file_out = $static_css.$file.'.css';
			$less = new \lessc;
			$less ->compileFile($file_in, $file_out); 
			//$less ->checkedCompile($file_in, $file_out);			
		}
		 
		
		// echo $file_in.'---'.$file_out;
		
		 // die('ok ddd');
	}
    
    public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
    
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
	 
}
