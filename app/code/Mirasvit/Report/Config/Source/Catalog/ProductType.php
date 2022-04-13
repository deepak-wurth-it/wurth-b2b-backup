<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Config\Source\Catalog;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ProductType implements OptionSourceInterface
{
    /**
     * @var ProductTypeListInterface
     */
    protected $productTypeList;


    /**
     * ProductType constructor.
     * @param ProductTypeListInterface $productTypeList
     */
    public function __construct(
        ProductTypeListInterface $productTypeList
    ) {
        $this->productTypeList = $productTypeList;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $types = $this->productTypeList->getProductTypes();

        foreach ($types as $type) {
            $result[] = [
                'label' => $type->getLabel(),
                'value' => $type->getName(),
            ];
        }

        return $result;
    }
}
