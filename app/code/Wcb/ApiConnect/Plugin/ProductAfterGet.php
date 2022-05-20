<?php
namespace Wcb\ApiConnect\Plugin;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Wcb\Checkout\Helper\Data;
use Wcb\Base\Helper\Data as WCBHELPER;

class ProductAfterGet
{

    const PRODUCT_CODE ='product_code';
    /**
     * @var Data
     */
    protected $_helperData;
    /**
     * @var WCBHELPER
     */
    private $_wcbHelper;

    /**
     * @param ProductExtensionFactory $extensionFactory
     */
    public function __construct(
        Data $helperData,
        WCBHELPER $_wcbHelper
    ) {
        $this->_helperData = $helperData;
        $this->_wcbHelper = $_wcbHelper;
    }
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    ) {
        $getBaseUnitOfMeasureId = $entity->getBaseUnitOfMeasureId();
        $ourCustomData = $this->_helperData->getType($entity->getBaseUnitOfMeasureId());
        $data[] = ['id'=>$getBaseUnitOfMeasureId,'value'=>$ourCustomData];
        $extensionAttributes = $entity->getExtensionAttributes(); /** get current extension attributes from entity **/
        $extensionAttributes->setData('sales_unit_of_measure_id_value', $data);

        $productCode= $entity->getProductCode();
        $pdf[]= array(
            "flip_catalog"=>$this->_wcbHelper->getCatalogFlipPdfUrl().$productCode
            );

        $extensionAttributes->setData('product_pdf', $pdf);
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }
}
