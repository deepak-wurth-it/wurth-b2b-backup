<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pim\Category\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Grid\Container
{

 protected function _construct()
	{
		$this->_controller = 'adminhtml_index';
		$this->_blockGroup = 'Pim_Category';
		$this->_headerText = __('Pim Category List ');
               parent::_construct();
	}
}
