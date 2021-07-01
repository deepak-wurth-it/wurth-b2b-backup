<?php
/**
* Copyright © 2016 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/


namespace Plazathemes\InstagramGallery\Model\Config\Source;

class Target implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => '_self', 'label' => __('Same Window')],
			['value' => '_blank', 'label' => __('New Window')]
		];
	}
}