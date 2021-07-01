<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Plazathemes\Override\Model;

class Category extends \Magento\Catalog\Model\Category
{
    
	/**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl2($image_type)
    {
        $url = false;
		if($image_type == 'image')
			$image = $this->getImage();
		if($image_type == 'thumb_nail')
			$image = $this->getThumbNail();
		if($image_type == 'thumb_popular')
			$image = $this->getThumbPopular();	
		if($image_type == 'categorytab_image')
			$image = $this->getCategorytabImage();	
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }
    
}
