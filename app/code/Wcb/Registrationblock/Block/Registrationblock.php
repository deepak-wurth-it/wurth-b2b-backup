<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Wcb\Registrationblock\Block;

class Registrationblock extends \Magento\Framework\View\Element\Template {
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_scopeConfig = $context->getScopeConfig();
	}
	public function isEnabled()
	{
		return $this->_scopeConfig->getValue('wcbregister/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getBlockId()
	{
		return $this->_scopeConfig->getValue('wcbregister/general/block', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
}
