<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Attribute\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
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
        \Pim\Attribute\Model\AttributeValuesFactory $AttributeValuesFactory,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement,
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement,
        LoggerInterface $logger




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
        $this->attributeManagement = $attributeManagement;
        $this->eavSetup = $eavSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->product = $product;
        $this->logger = $logger;
        $this->attributeSetManagement = $attributeSetManagement;




    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install()
    {

        $objAttributes = $this->attributeFactory->create();
        $collection = $objAttributes->getCollection();
            //->addFieldToFilter('Active', ['eq' => '1']);;
        if ($collection->getSize() && $collection->count()) {

            $attributeCount = 0;
            foreach ($collection as $item) {
                echo 'Attribute Create Start =>>'.$item->getData('Id').PHP_EOL;;
                $data['frontend_label'] = $item->getData('Name');
                $data['frontend_input'] = 'multiselect';
                $data['is_required'] = '0';
                $data['default'] = '';
                $attributeCode = $item->getData('ExternalId');
                $data['attribute_code'] =  'attr_'.$attributeCode;
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
                $data['group'] = 'Product Details';

                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $attribute = $this->eavConfig->getAttribute('catalog_product', $data['attribute_code']);

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

                try {
                    $attribute->save();
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    echo $e->getMessage().PHP_EOL;
                }
                $attributeId = $attribute->getId();

                // get default attribute set id
                $eavSetup = $this->eavSetupFactory->create();

                $attributeSetId = $defaultSetId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
                    ->getDefaultAttributeSetId();
                $attributeGroupName = $data['group'];
                $attributeCount++;
                $attributeSortOrder = $attributeCount + 999;
                $this->assignAttributeToGroup($eavSetup,$attributeSetId,$attributeId,$attributeGroupName,$attributeSortOrder);
                $item->setData('magento_attribute_code_b2b',$attribute->getAttributeCode());
                $item->setData('magento_attribute_id_b2b',$attribute->getId());

                try {
                    $item->save();
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());

                    echo $e->getMessage().PHP_EOL;
                }
                echo 'Finished Attribute Create For =>>'.$item->getData('Id').PHP_EOL;

            }


        }

        $this->eavConfig->clear();
    }

    /**
     * @param $attribute
     * @param $data
     * @return array|array[]|null
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
     * @param $values
     * @return array|array[]
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
     * @return int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEntityTypeId()
    {
        if (!$this->entityTypeId) {
            $this->entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        }
        return $this->entityTypeId;
    }

    /**
     * @param $setName
     * @return bool|\Magento\Eav\Model\Entity\Attribute\Set|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
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

            try {
                $attributeSet->save();
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());

                echo $e->getMessage().PHP_EOL;
            }            $defaultSetId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
                ->getDefaultAttributeSetId();
            $attributeSet->initFromSkeleton($defaultSetId);
            try {
                $attributeSet->save();
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());

                echo $e->getMessage().PHP_EOL;
            }
        }
        return $attributeSet;
    }


    public function assignAttributeToGroup($eavSetup,$attributeSetId,$attributeId,$attributeGroupName,$attributeSortOrder){
        $eavSetup->addAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $attributeGroupName, // attribute group name
            null // sort order
        );

        // add attribute to group
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $attributeGroupName, // attribute group
            $attributeId, // attribute code
            $attributeSortOrder// sort order
        );
    }


}
