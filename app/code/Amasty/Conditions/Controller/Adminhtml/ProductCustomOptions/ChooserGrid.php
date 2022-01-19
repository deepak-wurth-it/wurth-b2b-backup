<?php

namespace Amasty\Conditions\Controller\Adminhtml\ProductCustomOptions;

/**
 * Custom Options Grid Controller Action
 * @since 1.4.0
 */
class ChooserGrid extends \Magento\Backend\App\Action
{
    /**
     * Path to this action
     */
    const URL_PATH = "amasty_conditions/productCustomOptions/chooserGrid";

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';

    /**
     * Grid ajax action in chooser mode
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
