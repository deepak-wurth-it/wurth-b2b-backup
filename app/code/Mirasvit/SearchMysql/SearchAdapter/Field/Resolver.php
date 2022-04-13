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


namespace Mirasvit\SearchMysql\SearchAdapter\Field;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;

class Resolver
{
    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    public function __construct(
        AttributeCollection $attributeCollection,
        FieldFactory $fieldFactory
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $fields)
    {
        $resolvedFields = [];
        $this->attributeCollection->addFieldToFilter('attribute_code', ['in' => $fields]);
        foreach ($fields as $field) {
            if ('*' === $field) {
                $resolvedFields = [
                    $this->fieldFactory->create(
                        [
                            'attributeId' => null,
                            'column' => 'data_index',
                            'type' => Field::TYPE_FULLTEXT
                        ]
                    )
                ];
                break;
            }
            $attribute = $this->attributeCollection->getItemByColumnValue('attribute_code', $field);
            $attributeId = $attribute ? $attribute->getId() : 0;
            $resolvedFields[$field] = $this->fieldFactory->create(
                [
                    'attributeId' => $attributeId,
                    'column' => 'data_index',
                    'type' => Field::TYPE_FULLTEXT
                ]
            );
        }
        return $resolvedFields;
    }
}

