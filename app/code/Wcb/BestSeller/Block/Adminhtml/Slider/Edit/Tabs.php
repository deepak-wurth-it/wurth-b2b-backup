<?php

namespace Wcb\BestSeller\Block\Adminhtml\Slider\Edit;

/**
 * Class Tabs
 * @package Wcb\BestSeller\Block\Adminhtml\Slider\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('slider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Best Seller Slider Information'));
    }
}
