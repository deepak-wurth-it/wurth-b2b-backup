<?php
namespace Wcb\ApiConnect\Plugin;
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Wcb\Checkout\Helper\Data;
class ProductAfterGet
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @param ProductExtensionFactory $extensionFactory
     */
    public function __construct(
        Data $helperData

    )
    {
        $this->_helperData = $helperData;
    }
    public function afterGet
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    ) {
    $getBaseUnitOfMeasureId = $entity->getBaseUnitOfMeasureId();
    $ourCustomData = $this->_helperData->getType($entity->getBaseUnitOfMeasureId());
        $data[] = array('id'=>$getBaseUnitOfMeasureId,'value'=>$ourCustomData);
        $extensionAttributes = $entity->getExtensionAttributes(); /** get current extension attributes from entity **/
        $extensionAttributes->setData('sales_unit_of_measure_id_value',$data);
         $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
}

}
