<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Controller\Adminhtml\Brand;

class MassStatus extends \Plazathemes\Brandslider\Controller\Adminhtml\Brand
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $brandIds = $this->getRequest()->getParam('brand');
        $status = $this->getRequest()->getParam('status');
        $storeViewId = $this->getRequest()->getParam('store');
        var_dump($status);
        // die;
        if (!is_array($brandIds) || empty($brandIds)) {
            $this->messageManager->addError(__('Please select brand(s).'));
        } else {
            try {
                foreach ($brandIds as $brandId) {
                    // $brand = $this->_brandFactory->create()->setStoreViewId($storeViewId)->load($brandId);
                    $brand = $this->_objectManager->create('Plazathemes\Brandslider\Model\Brand')->load($brandId);
                    $brand->setStatus($status)
                           ->setIsMassupdate(true)
                           ->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been changed status.', count($brandIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/', ['store' => $this->getRequest()->getParam("store")]);
    }
}
