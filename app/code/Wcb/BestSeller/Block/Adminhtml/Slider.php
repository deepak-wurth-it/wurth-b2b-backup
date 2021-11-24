<?php

namespace Wcb\BestSeller\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Slider
 * @package Wcb\BestSeller\Block\Adminhtml
 */
class Slider extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_slider';
        $this->_blockGroup = 'Wcb_BestSeller';
        $this->_headerText = __('Sliders');
        $this->_addButtonLabel = __('Create New Slider');
        parent::_construct();
    }
}
