<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Bannerslider\Controller\Adminhtml\Banner;

class Delete extends \Plazathemes\Bannerslider\Controller\Adminhtml\Banner
{
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('banner_id');
        try {
            $locator = $this->_objectManager->create('Plazathemes\Bannerslider\Model\Banner')->load($bannerId);
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
