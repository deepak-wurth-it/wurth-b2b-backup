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
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Backend\App\Action
{
   
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 public function execute()
    {

	
		$data = $this->getRequest()->getPostValue();
	
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Plazathemes\Hozmegamenu\Model\Hozmegamenu');

            $id = $this->getRequest()->getParam('hozmegamenu_id');
			
            if ($id) {
				$collection = $model->getCollection()->addFieldToFilter('store', $data['store']);
				if(count($collection)> 0)
				{
					foreach($collection as $item) {
						$id = $item['hozmegamenu_id'];
					}
					$model->load($id);
				}else{
					$data['hozmegamenu_id'] = null;
				}
            }
			
			if(isset($data['is_link'])) {
				$is_link = $data['is_link']; 
				$data['is_link'] = json_encode($is_link,true);
			
			}
			if(isset($data['items'])) {
				$items = $data['items']; 
				$data['items'] = json_encode($items,true);
			}
			
			if(isset($data['image'])) {
				$image = $data['image']; 
				$data['image'] = json_encode($image,true);
			}
            $model->setData($data);
			//echo "<pre>"; print_r($data); die;

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Hozmegamenu has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['hozmegamenu_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Hozmegamenu.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['hozmegamenu_id' => $this->getRequest()->getParam('hozmegamenu_id')]);
        }
        return $resultRedirect->setPath('*/*/');
		
    }
	

	
}
