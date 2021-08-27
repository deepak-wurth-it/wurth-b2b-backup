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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class BrandAttributeOptions implements ArrayInterface
{
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * BrandAttributeOptions constructor.
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getSelectAttributes();
    }

    /**
     * @return array
     */
    private function getSelectAttributes()
    {
        $options = [['value' => '0', 'label' => '~ Not selected ~']];
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->addIsFilterableFilter()->addOrder('attribute_code', 'asc');

        foreach ($attributeCollection as $attribute) {
            $options[] = ['value' => $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode() . ' (' . $attribute->getFrontendLabel() . ')'
            ];
        }

        return $options;
    }
}
