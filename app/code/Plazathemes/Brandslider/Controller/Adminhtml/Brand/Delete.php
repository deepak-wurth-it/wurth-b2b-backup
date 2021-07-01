<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Controller\Adminhtml\Brand;

class Delete extends \Plazathemes\Brandslider\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $brandId = $this->getRequest()->getParam('brand_id');
        try {
            $locator = $this->_objectManager->create('Plazathemes\Brandslider\Model\Brand')->load($brandId);
            $locator->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
