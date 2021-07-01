<?php
namespace Plazathemes\Template\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	public function getContentFromXmlFile($xmlFile = null, $node=null) {
		
		$dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $statickBlockData = array();
		$blocks = $dom->getElementsByTagName($node);
		$blockArrays = array();
		foreach($blocks as $name) {
			$blockArray = array();
			if($name->childNodes->length) {
				foreach($name->childNodes as $i) {
					$blockArray[$i->nodeName] = $i->nodeValue;
				}
			}

			$blockArrays[] = $blockArray;
		} 
		
		return $blockArrays; 
	}
	
	public function getConfigData($demo_temp = null) {
        
        $xmlPath = __DIR__ . '/Xml/data_config_demo'.$demo_temp.'.xml';
        $configData = $this->getContentFromXmlFile($xmlPath, 'default');
        if ($configData)
            return $configData;
        return array();

    }
	  
    
   public function getStaticBlockData() {
        $xmlPath = __DIR__ . '/Xml/data_static_blocks.xml';  
		//$xmlPath = 'E:/xampp/htdocs/magento2/demo100/app/code/Plazathemes/Template/Helper/Xml/data_static_blocks.xml';
        $statickBlockData = $this->getContentFromXmlFile($xmlPath, 'block');
        if ($statickBlockData)
            return $statickBlockData;
        return array();
    }
    
    public function getCmsPageData() {
        
        $xmlPath = __DIR__ . '/Xml/data_resources.xml';
        $statickBlockData = $this->getContentFromXmlFile($xmlPath, 'resource');
        if ($statickBlockData)
            return $statickBlockData;
        return array();

    }
	
	public function getBannerData() {

		 $xmlPath = __DIR__ . '/Xml/banner.xml';  
        $statickBlockData = $this->getContentFromXmlFile($xmlPath, 'record');

        if ($statickBlockData)
            return $statickBlockData;
        return array();
    }

    public function getBrandSliderData() {

        $xmlPath = __DIR__ . '/Xml/brandslider.xml';
        $statickBlockData = $this->getContentFromXmlFile($xmlPath, 'record');

        if ($statickBlockData)
            return $statickBlockData;
        return array();
    }
	
    
  
    public function haveBlockBefore($identifier = NULL,$blockModel) {
        //$stores = implode(',', $stores);
        $exist = $blockModel->getCollection()
                ->addFieldToFilter('identifier', array('eq' => $identifier))
                ->load();
        if (count($exist))
            return true;
        return false;
    }
	
	 public function haveBannerBefore($identifier = NULL,$blockModel) {
        //$stores = implode(',', $stores);
        $exist = $blockModel->getCollection()
                ->addFieldToFilter('banner_id', array('eq' => $identifier))
                ->load();
        if (count($exist))
            return true;
        return false;
    }
	
	public function haveBrandBefore($identifier = NULL,$blockModel) {
        //$stores = implode(',', $stores);
        $exist = $blockModel->getCollection()
                ->addFieldToFilter('brandslider_id', array('eq' => $identifier))
                ->load();
        if (count($exist))
            return true;
        return false;
    }
    
    public function haveBlockPageBefore($identifier = NULL,$cmsModel) {
        //$stores = implode(',', $stores);
        $exist = $cmsModel->getCollection()
                ->addFieldToFilter('identifier', array('eq' => $identifier))
                ->load();
        if (count($exist))
            return true;
        return false;
    }
    
    
    
    public function getNodeDataFromBlock($node = 'identifier', $blocks = array()) {
        
        $array_identifier = array();
        foreach($blocks as $block) {
            $identifier = $block[$node];
            $array_identifier[] = $identifier;
        
        }
        if($array_identifier)
            return $array_identifier;
        return array();
        
    }
    
    public function getNodeDataFromStaticBlock() {
       if($this->getNodeDataFromBlock('identifier', $this->getStaticBlockData())) 
               return $this->getNodeDataFromBlock('identifier', $this->getStaticBlockData());
       return array();
    }
     
      public function getNodeDataFromCmsPageBlock() {
       if($this->getNodeDataFromBlock('identifier', $this->getCmsPageData())) 
               return $this->getNodeDataFromBlock('identifier', $this->getCmsPageData());
       return array();
    }
    
    public function getOldConfigData(){
        $oldConfig = array(
            array(
                0=> 'default',
                1=> 'home'
            )
        );
        return $oldConfig;
    }
    

	 public function getAllStore() {
        $stores = Mage::app()->getStores();
        $storeIds = array();
	  	$storeIds[]= 0;
        foreach ($stores as $_store) {
			
				$storeIds[] = $_store->getId();
		}
        return $storeIds;
    }
}
