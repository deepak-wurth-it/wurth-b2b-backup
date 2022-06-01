<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\Catalog\Model\Product\Gallery;


use Magento\Catalog\Model\Product\Gallery\CreateHandler as CreateHandlerWcb;

/**
 * Create handler for catalog product gallery
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class CreateHandler extends CreateHandlerWcb
{
 
 
     

    /**
     * Process images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     * @since 101.0.0
     */
    protected function processNewAndExistingImages($product, array &$images){

		
		   $extAttributes = $product->getExtensionAttributes();
           $pim_picture_id = $extAttributes->getPimPictureId();
           
        foreach ($images as &$image) {
            if (empty($image['removed'])) {
                $data = $this->processNewImage($product, $image);

                if (!$product->isObjectNew()) {
                    $this->resourceModel->deleteGalleryValueInStore(
                        $image['value_id'],
                        $product->getData($this->metadata->getLinkField()),
                        $product->getStoreId()
                    );
                }
                // Add per store labels, position, disabled
                $data['value_id'] = $image['value_id'];
                $data['label'] = isset($image['label']) ? $image['label'] : '';
                $data['position'] = isset($image['position']) ? (int)$image['position'] : 0;
                $data['disabled'] = isset($image['disabled']) ? (int)$image['disabled'] : 0;
                $data['pim_picture_id'] = isset($pim_picture_id) ? (int)$pim_picture_id : 0;
                $data['store_id'] = (int)$product->getStoreId();

                $data[$this->metadata->getLinkField()] = (int)$product->getData($this->metadata->getLinkField());

                $this->resourceModel->insertGalleryValueInStore($data);
            }
        }
    }

    

    
}
