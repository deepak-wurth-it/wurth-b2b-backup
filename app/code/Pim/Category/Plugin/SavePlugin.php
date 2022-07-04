<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Plugin;

use Magento\Catalog\Controller\Adminhtml\Category\Save;


class SavePlugin
{
    //Disable save on pim category attribute
    public function beforeExecute(Save $subject)
    {
        $categoryPostData =  $subject->getRequest()->getPostValue();

        unset($categoryPostData['pim_category_parent_id']);
        unset($categoryPostData['pim_category_active_status']);
        unset($categoryPostData['pim_category_channel_id']);
        unset($categoryPostData['pim_category_code']);
        unset($categoryPostData['pim_category_external_id']);
        unset($categoryPostData['pim_category_id']);

        $subject->getRequest()->setPostValue($categoryPostData);
    }
}
