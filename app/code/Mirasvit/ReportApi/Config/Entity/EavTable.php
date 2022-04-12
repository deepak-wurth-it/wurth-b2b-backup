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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Entity;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Framework\App\CacheInterface;
use Mirasvit\ReportApi\Config\Loader\Map;
use Mirasvit\ReportApi\Service\TableService;

class EavTable extends Table
{
    /**
     * @var EavEntityFactory
     */
    private $eavEntityFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavFieldFactory
     */
    private $eavFieldFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Map
     */
    private $map;

    /**
     * EavTable constructor.
     * @param Map $map
     * @param EavFieldFactory $eavFieldFactory
     * @param EavEntityFactory $eavEntityFactory
     * @param CacheInterface $cache
     * @param Config $eavConfig
     * @param string $type
     * @param TableService $tableService
     * @param FieldFactory $fieldFactory
     * @param string $name
     * @param string $label
     * @param null $group
     * @param string $connection
     */
    public function __construct(
        Map $map,
        EavFieldFactory $eavFieldFactory,
        EavEntityFactory $eavEntityFactory,
        CacheInterface $cache,
        Config $eavConfig,
        $type,
        TableService $tableService,
        FieldFactory $fieldFactory,
        $name,
        $label,
        $group = null,
        $connection = 'default'
    ) {
        parent::__construct($tableService, $fieldFactory, $name, $label, $group, $connection);

        $this->map              = $map;
        $this->eavFieldFactory  = $eavFieldFactory;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->eavConfig        = $eavConfig;
        $this->cache            = $cache;

        $this->initByEntityType($type);
    }

    /**
     * @param string $entityType
     * @return void
     */
    protected function initByEntityType($entityType)
    {
        $eavData = $this->getEavData($entityType);

        foreach ($eavData as $attributeCode => $data) {
            $field = $this->eavFieldFactory->create([
                'table'        => $this,
                'name'         => $attributeCode,
                'entityTypeId' => $entityType,
            ]);

            $this->fieldsPool[$field->getName()] = $field;

            if ($data['label']) {
                $this->map->initColumn([
                    'name'    => $attributeCode,
                    'table'   => $this,
                    'type'    => $data['type'],
                    'options' => $data['options'],
                    'label'   => $data['label'],
                ]);
            }
        }
    }

    /**
     * @param string $entityType
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Json_Exception
     */
    private function getEavData($entityType)
    {
        $cacheKey = __CLASS__ . $entityType;

        $cache = $this->cache->load($cacheKey);
        if ($cache) {
            return \Zend_Json::decode($cache);
        }

        $data = [];

        $entityTypeId = (int)$this->eavEntityFactory->create()->setType($entityType)->getTypeId();

        $attributeCodes = $this->eavConfig->getEntityAttributeCodes($entityTypeId);

        foreach ($attributeCodes as $attributeCode) {
            if (in_array($attributeCode, ['category_ids', 'media_gallery'])) {
                continue;
            }

            $attribute = $this->eavConfig->getAttribute($entityTypeId, $attributeCode);

            $options = null;

            // To prevent issue with incorrectly migrated attributes from M1
            // (Type error occured when created the object: Magento\Eav\Model\Entity\Attribute\Source\Config)
            try {
                if ($attribute->getDefaultFrontendLabel()) {
                    if ($attribute->usesSource()) {
                        if ($attribute->getSourceModel() && !class_exists($attribute->getSourceModel())) {
                            continue;
                        } else {
                            if (method_exists($attribute->getSource(), 'toOptionArray')) {
                                $options = $attribute->setStoreId(0)->getSource()->toOptionArray();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {}

            $data[$attributeCode] = [
                'name'    => $attributeCode,
                'type'    => $this->resolveType($attribute->getFrontendInput()),
                'label'   => $attribute->getDefaultFrontendLabel(),
                'options' => $options,
            ];
        }

        $this->cache->save(\Zend_Json::encode($data), $cacheKey);

        return $data;
    }

    /**
     * @param string $typeName
     * @return string
     */
    public function resolveType($typeName)
    {
        switch ($typeName) {
            case 'text':
            case 'boolean':
            case 'hidden':
            case 'multiline':
            case 'textarea':
            case 'gallery':
            case 'media_image':
                $typeName = 'string';
                break;
            case 'select':
            case 'multiselect':
                $typeName = 'select';
                break;
            case 'price':
                $typeName = 'money';
                break;
            default:
                $typeName = 'string';
        }

        return $typeName;
    }
}
