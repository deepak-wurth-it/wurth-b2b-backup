<?php
namespace Wcb\Catalogslider\Block\Adminhtml\Catalogslider\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalogslider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Catalogslider Information'));
    }
}