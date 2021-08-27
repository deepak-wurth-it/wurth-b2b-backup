<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Attribute\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeProcessor
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var int
     */
    protected $entityTypeId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Pim\Attribute\Model\AttributeFactory $AttributeFactory,
        \Pim\Attribute\Model\AttributeValuesFactory $AttributeValuesFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productHelper = $productHelper;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->attributeFactory = $AttributeFactory; 
        $this->attributeValuesFactory = $AttributeValuesFactory;

    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {

        $objAttributes = $this->attributeFactory->create();
        $collection = $objAttributes->getCollection()
            ->addFieldToFilter('magento_attribute_type', ['neq' => 'NULL']);
            //->addFieldToFilter('Active', ['eq' => '1']);;
           // echo $collection->getSize();exit;
        if ($collection->getSize() && $collection->count()) {

            
            foreach ($collection as $item) {

                $data['frontend_label'] = $item->getData('Name');
                $translatedStringAttributeCode = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $data['frontend_label']);//replacing special characters like à->a, è->e
                $data['frontend_input'] = strtolower($item->getData('magento_attribute_type'));
                $data['is_required'] = '0';
                $data['default'] = '';
                $attributeCode = $translatedStringAttributeCode;
                $attributeCode = strtolower($attributeCode);
                $attributeCode = preg_replace("/[^ \w]+/", "", $attributeCode); // Replace all characters except letters, numbers, spaces and underscores
                $attributeCode = str_replace(' ', '_', $attributeCode);               
                $data['attribute_code'] =  $attributeCode;
                $data['is_global'] = '1';
                $data['default_value_text'] = '';
                $data['default_value_yesno'] = '0';
                $data['default_value_date'] = '';
                $data['default_value_textarea'] = '';
                $data['is_unique'] = '0';
                $data['is_searchable'] = '1';
                $data['is_visible_in_advanced_search'] = '1';
                $data['is_comparable'] = '1';
                $data['is_filterable'] = '1';
                $data['is_filterable_in_search'] = '1';
                $data['position'] = '';
                $data['is_used_for_promo_rules'] = '1';
                $data['is_html_allowed_on_front'] = '1';
                $data['is_visible_on_front'] = '1';
                $data['used_in_product_listing'] = '0';
                $data['used_for_sort_by'] = '0';
                $data['attribute_set'] = 'Default';

            
             
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
                if (!$attribute) {
                    $attribute = $this->attributeFactory->create();
                }

                $data['option'] = $this->getOption($attribute, $item->getData());

                $frontendLabel = $data['frontend_label'];

                $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );
              
                $data['backend_type'] = $attribute->getBackendTypeByInput($data['frontend_input']);

                $attribute->addData($data);
                $attribute->setIsUserDefined(1);

                $attribute->setEntityTypeId($this->getEntityTypeId());
                $attribute->save();
                $attributeId = $attribute->getId();

                if (is_array($data['attribute_set'])) {
                    foreach ($data['attribute_set'] as $setName) {
                        $setName = trim($setName);
                        $attributeSet = $this->processAttributeSet($setName);
                        $attributeGroupId = $attributeSet->getDefaultGroupId();

                        $attribute = $this->attributeFactory->create()->load($attributeId);
                        $attribute
                            ->setAttributeGroupId($attributeGroupId)
                            ->setAttributeSetId($attributeSet->getId())
                            ->setEntityTypeId($this->getEntityTypeId())
                            //->setSortOrder($attributeCount + 999)
                            ->save();
                    }
                }
                $item->setData('magento_attribute_code_b2b',$attribute->getAttributeCode());
                $item->setData('magento_attribute_id_b2b',$attribute->getId());
                $item->save();

            }
        }

        $this->eavConfig->clear();
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $data
     * @return array
     */
    protected function getOption($attribute, $data)
    {  
        $objCollection = $this->attributeValuesFactory->create();
        $collection = $objCollection->getCollection()
            ->addFieldToFilter('AttributeId', ['eq' => $data['Id']])
            ->addFieldToFilter('Active', ['eq' => '1']);
        $optionsValue = $collection->getColumnValues('Value');
    
       
        $result = [];
        $data['option'] = $optionsValue;
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $options */
        $options = $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attribute->getId())
            ->setPositionOrder('asc', true)
            ->load();
        foreach ($data['option'] as $value) {
            if (!$options->getItemByColumnValue('value', $value)) {
                $result[] = trim($value);
            }
        }
        return $result ? $this->convertOption($result) : $result;
    }



    /**
     * Converting attribute options from csv to correct sql values
     *
     * @param array $values
     * @return array
     */
    protected function convertOption($values)
    {
        $result = ['order' => [], 'value' => []];
        $i = 0;
        foreach ($values as $value) {
            $result['order']['option_' . $i] = (string)$i;
            $result['value']['option_' . $i] = [0 => $value, 1 => ''];
            $i++;
        }
        return $result;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Model\Exception
     */
    protected function getEntityTypeId()
    {
        if (!$this->entityTypeId) {
            $this->entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        }
        return $this->entityTypeId;
    }

    /**
     * Loads attribute set by name if attribute with such name exists
     * Otherwise creates the attribute set with $setName name and return it
     *
     * @param string $setName
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    protected function processAttributeSet($setName)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $this->getEntityTypeId())
            ->addFieldToFilter('attribute_set_name', $setName)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (!$attributeSet) {
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setEntityTypeId($this->getEntityTypeId());
            $attributeSet->setAttributeSetName($setName);
            $attributeSet->save();
            $defaultSetId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
                ->getDefaultAttributeSetId();
            $attributeSet->initFromSkeleton($defaultSetId);
            $attributeSet->save();
        }
        return $attributeSet;
    }
}
