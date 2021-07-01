<?php
namespace Plazathemes\Template\Model;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
class Template
{
		protected $_scopeConfig;

		protected $_storeManager;
						
		protected $_configFactory;
		
	public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
    }

    public function saveConfigDesgin($helper,$store=NULL,$website = NULL,$demo_temp =null)
    {
        // Default response
			$messages = new DataObject([
				'is_valid' => false,
				'request_success' => false,
				'request_message' => __('Error during Import '),
			]);

        try {
			
			$configData = $helper->getConfigData($demo_temp);
		
            $scope = "default";
            $scope_id = 0;
            if ($store && $store > 0) // store level
            {
                $scope = "stores";
                $scope_id = $store;
            }
            elseif ($website && $website > 0) // website level
            {
                $scope = "websites";
                $scope_id = $website;
            }
			//echo "<pre>"; print_r($configData[0]); die; 
			foreach ($configData[0] as $key => $config) {
				if(isset($config) && $config!=null) 
					$this->_configFactory->saveConfig('web/default/'.$key ,$config,$scope,$scope_id);
		  
			}
		
		
            

            $messages->setIsValid(true);
            $messages->setRequestSuccess(true);

            if ($messages->getIsValid()) {
                $messages->setRequestMessage(__('Success to Import '));
            } else {
                $messages->setRequestMessage(__('Error during Import '));
            }
        } catch (\Exception $exception) {
            $messages->setIsValid(false);
            $messages->setRequestMessage($exception->getMessage());
        }

        return $messages;
    }

      public function saveStaticBlock($store = NULL,$helper,$model) {
	
			$staticData = $helper->getStaticBlockData();

			foreach ($staticData as $block) {
				$block['stores'] = $store;
				if (!$helper->haveBlockBefore($block['identifier'],$model)) {
					$model->setData($block)->save();
				} else {
					 $model->load($block['identifier'])->setStores($store)->save();
				}
				  
			}
			
			//die('Add Block Successfully!');
		
       }
	   
	    public function SaveCmsPage($store = array(0),$helper,$model) {
	
			$cmsData = $helper->getCmsPageData(); 
			foreach ($cmsData as $block) {
				$block['stores'] = $store;
				if (!$helper->haveBlockPageBefore($block['identifier'],$model)) {
					//echo "<pre>"; print_r($block); die; 
					$model->setData($block)->save(); 
				} else {
					 $model->load($block['identifier'])->setStores($store)->save();
				}
				  
			}
			
		
       }
	   
		public function saveBanner($store = array(0),$helper, $model) {
			
			$banerData = $helper->getBannerData(); 
			foreach($banerData as $banner) {
				if($banner){
				
						$model->setData($banner)->save(); 
				}
			}
	
		}
		
		
	 public function saveBrand($store = array(0),$helper, $model) {
			
			$brandData = $helper->getBrandSliderData(); 
			foreach($brandData as $banner) {
				if($banner){
				
						$model->setData($banner)->save(); 
				}
			}
	
		}

	   
	   
	public function deleteCmsPageBlock($key = NULL, $stores = NULL, $cmsModel) {
		$cmsModel->load($key);
        $storesOld = $cmsModel->getStoreId(); 
        $storeNew = array();
		if(count($storesOld)) {
			foreach ($storesOld as $storeId) {
				if (!in_array($storeId, $stores)) {
					$storeNew[] = $storeId;
				}
			}
		}

        if (in_array(0, $stores)) {
           $cmsModel->delete();
			//echo "Delete Successfully Page Identify =".$key."<br>" ;
        } else {
            $cmsModel->setStores($storeNew)->save();
        }
    }

    public function deleteStaticBlock($key = NULL, $stores = NULL,$blockModel) {
        $blockModel->load($key);
        $storesOld = $blockModel->getStoreId();
        $storeNew = array();
		if(count($storesOld)) {
			foreach ($storesOld as $storeId) {
				if (!in_array($storeId, $stores)) {
					$storeNew[] = $storeId;
				}
			}
		}

        if (in_array(0, $stores)) {
            $blockModel->delete();
			//echo "Delete Successfully Block Identify =".$key."<br>" ;
        } else {
            $blockModel->setStores($storeNew)->save();
        }
    }
	
	public function deleteBanner($stores = null,$bannerModel) {
		
		$bannerCollection = $bannerModel->getCollection();
		if(count($bannerCollection) >0 ) {
			foreach($bannerCollection as $banner) {
					$banner->delete(); 
			}
		}
		return; 
		

	}
	
	public function deleteBrand($stores = null,$brandModel) {
		
		$brandCollection = $brandModel->getCollection();
		if(count($brandCollection) >0 ) {
			foreach($brandCollection as $brand) {
					$brand->delete(); 
			}
		}
		return; 
		

	}
	
   
}