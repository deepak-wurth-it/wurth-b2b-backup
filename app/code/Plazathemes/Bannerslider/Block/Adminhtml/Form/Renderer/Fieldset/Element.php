<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

/**
 * Catalog fieldset element renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Plazathemes\Bannerslider\Block\Adminhtml\Form\Renderer\Fieldset;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element {
	/**
	 * Initialize block template
	 */
	protected $_template = 'Plazathemes_Bannerslider::form/renderer/fieldset/element.phtml';

	/**
	 *
	 * @return string
	 */
	public function getElementName() {
		return $this->getElement()->getName();
	}

	/**
	 * @return string
	 */
	public function getElementStoreViewId() {
		return $this->getElement()->getStoreViewId();
	}

	/**
	 * Check "Use default" checkbox display availability
	 *
	 * @return bool
	 */
	public function canDisplayUseDefault() {
		return ($this->getRequest()->getParam('store') && $this->getElement()->getDateFormat() == null && $this->getElementName() != 'slider_id') ? true : false;
	}

	/**
	 * Check default value usage fact
	 *
	 * @return bool
	 */
	public function usedDefault() {
		return $this->getElementStoreViewId() ? false : true;
	}

	/**
	 * Disable field in default value using case
	 *
	 * @return \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
	 */
	public function checkFieldDisable() {
		if (!$this->getElementStoreViewId() && $this->getElementName() != 'banner_id' && $this->canDisplayUseDefault() && $this->usedDefault()) {
			$this->getElement()->setDisabled(true);
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getScopeLabel() {
		if ($this->getElement()->getDateFormat() != null || $this->getElementName() == 'slider_id') {
			return '[GLOBAL]';
		}
		return '[STORE VIEW]';
	}

	/**
	 * Retrieve element label html
	 *
	 * @return string
	 */
	public function getElementLabelHtml() {
		$element = $this->getElement();
		$label = $element->getLabel();
		if (!empty($label)) {
			$element->setLabel(__($label));
		}
		return $element->getLabelHtml();
	}

	/**
	 * Retrieve element html
	 *
	 * @return string
	 */
	public function getElementHtml() {
		return $this->getElement()->getElementHtml();
	}

	/**
	 * Default sore ID getter
	 *
	 * @return integer
	 */
	protected function _getDefaultStoreId() {
		return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
	}
}
