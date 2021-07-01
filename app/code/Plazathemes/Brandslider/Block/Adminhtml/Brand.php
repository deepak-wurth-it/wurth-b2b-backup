<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Block\Adminhtml;

class Brand extends \Magento\Backend\Block\Widget\Grid\Container {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct() {

		$this->_controller = 'adminhtml_brand';
		$this->_blockGroup = 'Plazathemes_Brandslider';
		$this->_headerText = __('Brands');
		$this->_addButtonLabel = __('Add New Brand');
		parent::_construct();
	}
}
