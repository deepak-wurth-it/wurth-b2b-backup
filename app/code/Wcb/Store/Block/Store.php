<?php

/**
 *
 * @category  Wcb
 * @package   Wcb_Store
 * @author    Deepak Kumar <deepak.kumar.rai@wuerth-it.com>
 * @copyright 2022 Wuerth-IT
 */

namespace Wcb\Store\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\App\Request\Http;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Registry;
use \Magento\Variable\Model\Variable;
use \Psr\Log\LoggerInterface;

class Store extends Template {

	
   /**
    * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
    */
    protected $layoutProcessors;
    protected $collectionFactory;
    protected $storeManagerInterface;

    public function __construct(
            
    \Magento\Framework\View\Element\Template\Context $context, 
    \Wcb\Store\Model\ResourceModel\Store\CollectionFactory $collectionFactory, 
    \Magento\Catalog\Model\Session $catalogSession, 
    StoreManagerInterface $StoreManagerInterface,
    Http $request, 
    Registry $registry, 
    Variable $CustomVariable,
    LoggerInterface $logger,
    array $layoutProcessors = [],
    array $data = []
            
    ) {

       
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->customVariable = $CustomVariable;
        $this->catalogSession = $catalogSession;
        $this->storeManagerInterface = $StoreManagerInterface;

        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
   }
		public function getJsLayout()
	   {
		   foreach ($this->layoutProcessors as $processor) {
			   $this->jsLayout = $processor->process($this->jsLayout);
		   }
		   return \Zend_Json::encode($this->jsLayout);
	   }

    public function getActiveStore() {
        try {
            $id = '';
            $route = $this->request->getControllerName();
            $category = $this->_registry->registry('current_category');
            $id = (int) (!empty($category) ? $category->getId() : false);

            $display_pages = ($id === 32 && trim($route) === 'category') ? 'alldeals' :
                    ($route === 'account' ? 'myaccount' :
                            ($route === 'result' ? 'search' :
                                    ($route == 'product' ? 'pdp' : false)));


            $cityId = $this->catalogSession->getCityId();
            $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('cities', array('finset' => $cityId))
                    ->addFieldToFilter('display_pages', array('finset' => trim($display_pages)))
                    ->setPageSize(1);

            return $collection;
        } catch (\Exception $e) {
            $this->_logger->error('Issue in getActiveStore method ', ['message' => $e->getMessage()]);
        }
    }
    
    
    public function getStores(){
		     $stores = "";
		
		     $storesCollection = $this->collectionFactory->create()
             //->addFieldToFilter('name')
		     ->addFieldToFilter('status', 1);
		     
		    
			return $storesCollection;
		
		}

    public function getMediaDirectoryUrl() {

        $media_dir = $this->storeManagerInterface->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $media_dir;
    }

    public function getStoresArray(){
        $stores = [];
        $item = [];
        $item = $this->getStores()->toArray()['items'];
        foreach( $item as $key=>$row){
                $stores[] = (array)$row;
        }
        return $stores;
    }

}

