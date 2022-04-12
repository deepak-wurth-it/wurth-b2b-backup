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



namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Config as EavConfig;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

class Index extends AbstractIndex
{
    private $attributes = [];

    private $attributeToCode;

    private $attributeCollectionFactory;

    private $layerResolver;

    private $eavConfig;

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        LayerResolver $layerResolver,
        EavConfig $eavConfig,
        Context $context
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->layerResolver              = $layerResolver;
        $this->eavConfig                  = $eavConfig;

        parent::__construct($context);
    }

    public function getName(): string
    {
        return 'Magento / Product';
    }

    public function getIdentifier(): string
    {
        return 'catalogsearch_fulltext';
    }

    /**
     * @param bool $extended
     *
     * @return array
     */
    public function getAttributes($extended = false): array
    {
        if (!$this->attributes) {
            $collection = $this->attributeCollectionFactory->create()
                ->addVisibleFilter()
                ->setOrder('attribute_id', 'asc');

            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($collection as $attribute) {
                $allLockedFields = $this->eavConfig->get(
                    $attribute->getEntityType()->getEntityTypeCode() . '/attributes/' . $attribute->getAttributeCode()
                );
                if (!is_array($allLockedFields)) {
                    $allLockedFields = [];
                }

                if ($attribute->getDefaultFrontendLabel() && !isset($allLockedFields['is_searchable'])) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute->getDefaultFrontendLabel();
                }
            }
        }

        $result = $this->attributes;

        if ($extended) {
            $result['visibility']   = '';
            $result['options']      = '';
            $result['category_ids'] = '';
            $result['status']       = '';
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode($attributeId)
    {
        if (!isset($this->attributeToCode[$attributeId])) {
            $attribute = $this->attributeCollectionFactory->create()
                ->getItemByColumnValue('attribute_id', $attributeId);

            $this->attributeToCode[$attributeId] = $attribute['attribute_code'];
        }

        return $this->attributeToCode[$attributeId];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeWeights(): array
    {
        $result     = [];
        $collection = $this->attributeCollectionFactory->create()
            ->addVisibleFilter()
            ->setOrder('search_weight', 'desc');

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            if ($attribute->getIsSearchable() && !in_array($attribute->getFrontendInput(), ['price', 'weight', 'date', 'datetime'])) {
                $result[$attribute->getAttributeCode()] = $attribute->getSearchWeight();
            }
        }

        return $result;
    }

    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    public function buildSearchCollection(): Collection
    {
        /** @var \Magento\Catalog\Model\Layer\Search $layer */
        $layer = $this->layerResolver->get();

        if ($this->context->getConfig()->isMultiStoreModeEnabled()) {
            $originalCategory = $layer->getData('current_category');
            // set random category for multi-store mode
            // this mode can be not compatible with some layered navigation
            $category = $this->context->getObjectManager()->create('Magento\Catalog\Model\Category');
            $category->setId(rand(100000, 900000));
            $layer->setData('current_category', $category);

            $collection = $layer->getProductCollection();

            $layer->setData('current_category', $originalCategory);
        } else {
            $collection = $layer->getProductCollection();
        }

        if (strpos((string)$collection->getSelect(), '`e`') !== false) {
            $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');
        } else {
            $this->context->getSearcher()->joinMatches($collection, 'main_table.entity_id');
        }

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function reindexAll(int $storeId = null): InstanceInterface
    {
        $configData = [
            'fieldsets'  => [],
            'indexer_id' => 'catalogsearch_fulltext',
        ];

        /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext $fulltext */
        $fulltext = $this->context->getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Indexer\Fulltext::class, [
                'data' => $configData,
            ]);

        $fulltext->executeFull();

        $this->getIndex()
            ->setStatus(IndexInterface::STATUS_READY)
            ->save();

        return $this;
    }
}
