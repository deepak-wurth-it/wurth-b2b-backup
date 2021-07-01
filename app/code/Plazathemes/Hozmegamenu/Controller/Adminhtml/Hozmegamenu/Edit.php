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
namespace Plazathemes\Hozmegamenu\Controller\Adminhtml\Hozmegamenu;
use Magento\Backend\App\Action;
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	public function execute()
    {

		// $id = 1;
		$store = $this->getRequest()->getParam('store');
        $model = $this->_objectManager->create('Plazathemes\Hozmegamenu\Model\Hozmegamenu');
		if($store)
		{
			$collection = $model->getCollection()->addFieldToFilter('store', $store);
			if(count($collection)<= 0)
				$collection = $model->getCollection()->addFieldToFilter('store', '0');
		}
		else
			$collection = $model->getCollection()->addFieldToFilter('store', '0');
		
		$registryObject = $this->_objectManager->get('Magento\Framework\Registry');
		if( count($collection)> 0 ) {
			foreach($collection as $item) {
				$id = $item['hozmegamenu_id'];
			}
			
			
			// 2. Initial checking
			if ($id) {
				$model->load($id);
				if (!$model->getId()) {
					$this->messageManager->addError(__('This Hozmegamenu no longer exists.'));
					$this->_redirect('*/*/');
					return;
				}
			}
		}
        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		//echo "<pre>"; print_r($model->getData()); die;
        if (!empty($data)) {
            $model->setData($data);
        }
		
		
		$registryObject->register('hozmegamenu', $model);	
	
		$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
