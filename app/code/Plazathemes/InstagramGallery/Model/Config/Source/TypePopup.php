<?php
/**
* Copyright © 2016 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\InstagramGallery\Model\Config\Source;

class TypePopup implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value'=>'thumb', 'label'=>__('Thumbnail')],
			['value'=>'button', 'label'=>__('Button')]

		];
	}
}