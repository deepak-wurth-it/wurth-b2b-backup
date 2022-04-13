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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Index\Magento\Catalog\Attribute;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Source implements ArrayInterface
{
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var bool
     */
    private $toBuild = true;

    /**
     * Source constructor.
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
        $options = [];

        if ($this->toBuild) {
            $this->toBuild = false;

            $collection = $this->attributeCollectionFactory->create()
                ->addVisibleFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->setOrder('attribute_id', 'asc');

            foreach ($collection as $attribute) {
                $attributeOptions = $attribute->getSource()->getAllOptions(true);
                if (count($attributeOptions) > 1) {
                    $options[] = [
                        'value' => $attribute->getAttributeCode(),
                        'label' => $attribute->getDefaultFrontendLabel() . ' [' . $attribute->getAttributeCode() . ']',
                    ];
                }
            }
        }

        return $options;
    }
}
