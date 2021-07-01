<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Plazathemes\Template\Controller\Adminhtml\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Backend\App\Action
{
   
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 public function execute()
    {

		try {
			
				$helper = $this->_objectManager->get('\Plazathemes\Template\Helper\Data');
				$modelTemplate = $this->_objectManager->get('\Plazathemes\Template\Model\Template');
				$block_model = $this->_objectManager->create('Magento\Cms\Model\Block');
				$banner_model = $this->_objectManager->create('Plazathemes\Bannerslider\Model\Banner');
				$brand_model = $this->_objectManager->create('Plazathemes\Brandslider\Model\Brand');
				$cms_model = $this->_objectManager->create('Magento\Cms\Model\Page');
				//$config_model = $this->_objectManager->create('Magento\Config\Model\Config'); 
				$store = array(0); 
				$store = $this->getRequest()->getParam('stores');
				$template = $this->getRequest()->getParam('template'); 
				if($template == 1) { 
					
					$modelTemplate ->saveBanner($store, $helper , $banner_model);
					$modelTemplate ->saveBrand($store, $helper , $brand_model);
					//die(__Method__); 
					// Install Template
					//Create Static Blocks 
					$modelTemplate->saveStaticBlock($store,$helper,$block_model);
					//Create Cms Page 
					$modelTemplate->SaveCmsPage($store,$helper,$cms_model);
					$demo_temp = $this->getRequest()->getParam('demo_temp');
					// save to config 
					$modelTemplate ->saveConfigDesgin($helper,$this->getRequest()->getParam('current_store'),$this->getRequest()->getParam('current_website'),$demo_temp);
					$this->messageManager->addSuccess(__('The Template Demo '.$demo_temp.'  has been saved successfully.'));
				} elseif($template == 2)  { 
				
					// Uninstall Template
					//uninstall static block
					$identityFromStatic = $helper->getNodeDataFromStaticBlock();
				
					foreach ($identityFromStatic as $keyStatic) {
						$modelTemplate->deleteStaticBlock($keyStatic, $store,$block_model);
					}
					
					//uninstall cms page block
					$identityFromCmsPage = $helper->getNodeDataFromCmsPageBlock();
					foreach ($identityFromCmsPage as $keyPage) {
						$modelTemplate->deleteCmsPageBlock($keyPage,$store,$cms_model);
					}
					//uninstall Banner 
					$modelTemplate->deleteBanner($store,$banner_model);
					//uninstall Brand
					$modelTemplate->deleteBrand($store,$brand_model);
					
					$this->messageManager->addSuccess(__('The Template has uninstalled successfully.'));
					
				}
		
		  } catch (\Exception $exception) {
				$this->messageManager->addSuccess($exception->getMessage());
        }
	
		
		
	//	echo "<pre>"; print_r($data); die;
     
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
	

	
}
