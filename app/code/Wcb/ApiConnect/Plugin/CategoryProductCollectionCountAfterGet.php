<?php
namespace Wcb\ApiConnect\Plugin;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryProductCollectionCountAfterGet
{

    const PRODUCT_COUNT ='product_count';


    /**
     * @param CategoryExtensionFactory $extensionFactory
     */
    public function __construct(
    ) {
    }
    public function afterGet(
        CategoryRepositoryInterface $subject,
        CategoryInterface $entity
    ) {


        $data[$entity->getEntityId()] = ['id'=>$entity->getEntityId(),'product_count'=>$entity->getProductCount(),'product_category_count'=>count($entity->getProductCollection())];
        $extensionAttributes = $entity->getExtensionAttributes(); /** get current extension attributes from entity **/
        $extensionAttributes->setData('category_product_collection_count', $data);


        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

}
