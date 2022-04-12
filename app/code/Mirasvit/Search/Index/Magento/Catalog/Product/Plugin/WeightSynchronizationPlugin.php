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



namespace Mirasvit\Search\Index\Magento\Catalog\Product\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Mirasvit\Search\Api\Data\IndexInterface;

class WeightSynchronizationPlugin
{

    private $entity;

    private $attributeCollectionFactory;

    private $eavAttribute;

    public function __construct(
        Entity $entity,
        AttributeCollectionFactory $attributeCollectionFactory,
        EavAttribute $eavAttribute
    ) {
        $this->entity                     = $entity;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavAttribute               = $eavAttribute;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterSave(object $subject, IndexInterface $index): IndexInterface
    {
        if ($index->getIdentifier() != 'catalogsearch_fulltext') {
            return $index;
        }

        $attributes = $index->getAttributes();

        if (!is_array($attributes) || count($attributes) == 0) {
            return $index;
        }

        $entityTypeId = $this->entity->setType(Product::ENTITY)->getTypeId();

        $collection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('is_searchable', 1);

        if (!array_key_exists('sku', $attributes)) {
            $attributes['sku'] = 1;
        }

        if (!array_key_exists('name', $attributes)) {
            $attributes['name'] = 1;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            if (!array_key_exists($attribute->getAttributeCode(), $attributes) && $attribute->getIsSearchable()) {
                $attribute->setIsSearchable(0)
                    ->save();
            }
        }

        foreach ($attributes as $code => $weight) {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->eavAttribute->loadByCode($entityTypeId, $code);
            if (!$attribute->getId()) {
                continue;
            }
            if ($attribute->getSearchWeight() != $weight || !$attribute->getIsSearchable()) {
                $attribute->setSearchWeight($weight)
                    ->setIsSearchable(1)
                    ->save();
            }
        }

        return $index;
    }
}
