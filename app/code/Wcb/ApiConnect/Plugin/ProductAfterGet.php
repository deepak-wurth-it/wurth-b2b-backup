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

        $productCode = $entity->getProductCode();
        $pdf[]= array(
            "flip_catalog"=>$this->_wcbHelper->getCatalogFlipPdfUrl().$productCode
            );

        $extensionAttributes->setData('product_pdf', $pdf);
        $extensionAttributes->setData('technical_information', $this->getCustomAttributesVisibleOnFront($entity));

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }
    public function getCustomAttributesVisibleOnFront($entity){
        $excludeAttr = [];
        $data = [];
        $attributes = $entity->getAttributes();

        foreach ($attributes as $attribute) {
           $optionId='';
             if ($this->isVisibleOnFrontend($attribute, $excludeAttr))
             {
                $code = $attribute->getAttributeCode();
                $value = $entity->getResource()->getAttributeRawValue($entity->getId(), $code, '1');
                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                } elseif ($attribute->getFrontendInput() == 'select') {
                    $value = $attribute->getSource()->getOptionText($value);

                    $attr = $entity->getResource()->getAttribute($code);
                    if ($attr->usesSource()) {
                        $optionId = $attr->getSource()->getOptionId($value);
                    }

                } elseif ($attribute->getFrontendInput() == 'multiselect') {
                 // added if condition in order or resolve the explode issue if value is empty.
                     if(!empty($value) && $value) {
                        $multiselectOptionsArray = explode(',', $value);
                     foreach ($multiselectOptionsArray as $k => $optionKey) {
                        $multiselectOptionsArray[$k] = $attribute->getSource()->getOptionText($optionKey);
                     }
                    $value = implode(', ', $multiselectOptionsArray);
                    $multiSelectValue = explode(', ', $value);

                        foreach ($multiSelectValue as $a => $attValue) {
                            $attr = $entity->getResource()->getAttribute($code);
                            if ($attr->usesSource()) {
                                $optionIdInfo = $attr->getSource()->getOptionId($attValue);
                                $attArray[$a] = $optionIdInfo;
                                $optionId = implode(', ', $attArray);
                            }
                        }
                     }
                 }
                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => $attribute->getFrontendLabel(),
                        'value' => __($value),
                        'options_value' => $optionId,
                        'visible_on_storefront' => $attribute->getIsVisibleOnFront()
                    ];
                }
                // if(!empty($value)){
                //     $product->setCustomAttribute($attribute->getAttributeCode(), $data);
                // }
             }
         }
         return $data;
    }
    protected function isVisibleOnFrontend(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        array $excludeAttr
    ) {
        return ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr));
    }
}
