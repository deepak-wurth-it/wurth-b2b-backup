<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Bannerslider\Block\Adminhtml;

class Banner extends \Magento\Backend\Block\Widget\Grid\Container {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct() {

		$this->_controller = 'adminhtml_banner';
		$this->_blockGroup = 'Plazathemes_Bannerslider';
		$this->_headerText = __('Banners');
		$this->_addButtonLabel = __('Add New Banner');
		parent::_construct();
	}
}
