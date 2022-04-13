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


declare(strict_types=1);

namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity as EavEntity;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
use Mirasvit\Search\Api\Data\Index\BatchDataMapperInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Index\AbstractBatchDataMapper;
use Mirasvit\Search\Index\Context;
use Mirasvit\Search\Api\Data\QueryConfigProviderInterface;

class BatchDataMapper extends AbstractBatchDataMapper implements BatchDataMapperInterface
{
    private $productDataMapper;

    private $eavEntity;

    private $attribute;

    private $searchableAttributes;

    private $configProvider;

    public function __construct(
        ProductDataMapper $productDataMapper,
        EavEntity $eavEntity,
        Attribute $attribute,
        QueryConfigProviderInterface $configProvider,
        Context $context
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->eavEntity         = $eavEntity;
        $this->attribute         = $attribute;
        $this->configProvider  = $configProvider;

        parent::__construct($context);
    }

    public function map(array $documentData, $storeId, array $context = [])
    {
        $documentData = $this->productDataMapper->map($documentData, $storeId, $context);
        $documentData = $this->recursiveMap($documentData, '/sku$|name$|description$/');

        $documentData = $this->addCategoryData($documentData);
        $documentData = $this->addCustomOptions($documentData);
        $documentData = $this->addBundledOptions($documentData);
        $documentData = $this->addProductIdData($documentData);

        return $documentData;
    }

    private function addCategoryData(array $documentData): array
    {
        if (!$this->getIndex()->getProperty('include_category')) {
            return $documentData;
        }

        $resource   = $this->context->getResource();
        $connection = $resource->getConnection();

        $entityTypeId = $this->eavEntity->setType(Category::ENTITY)->getTypeId();

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->attribute->loadByCode($entityTypeId, 'name');

        $table = $connection->describeTable($attribute->getBackend()->getTable());

        $pk = 'entity_id';

        if (isset($table['row_id'])) {
            $pk = 'row_id';
        }

        $productIds = array_keys($documentData);

        $valueSelect = $connection->select()
            ->from(
                ['cc' => $resource->getTableName('catalog_category_entity')],
                [new \Zend_Db_Expr("GROUP_CONCAT(vc.value SEPARATOR ' ')")]
            )
            ->joinLeft(
                ['vc' => $attribute->getBackend()->getTable()],
                'cc.entity_id = vc.' . $pk,
                []
            )
            ->where("LOCATE(CONCAT('/', CONCAT(cc.entity_id, '/')), CONCAT(ce.path, '/'))")
            ->where('vc.attribute_id = ?', $attribute->getId());

        $columns = [
            'product_id' => 'product_id',
            'category'   => new \Zend_Db_Expr('(' . $valueSelect . ')'),
        ];

        $select = $connection->select()
            ->from([$resource->getTableName('catalog_category_product')], $columns)
            ->joinLeft(
                ['ce' => $resource->getTableName('catalog_category_entity')],
                'category_id = ce.entity_id',
                []
            )
            ->where('product_id IN (?)', $productIds);

        foreach ($connection->fetchAll($select) as $row) {
            if (!isset($documentData[$row['product_id']]['_misc'])) {
                $documentData[$row['product_id']]['_misc'] = '';
            }

            if (is_array($documentData[$row['product_id']]['_misc'])) {
                $documentData[$row['product_id']]['_misc'] = implode(' ', strip_tags($documentData[$row['product_id']]['_misc']));
            }
            $documentData[$row['product_id']]['_misc'] .= ' ' . strip_tags((string) $row['category']);
        }

        return $documentData;
    }

    private function getIndex(): IndexInterface
    {
        return $this->context->getIndexRepository()->getByIdentifier('catalogsearch_fulltext');
    }

    private function addCustomOptions(array $documentData): array
    {
        if (!$this->getIndex()->getProperty('include_custom_options')) {
            return $documentData;
        }

        $resource   = $this->context->getResource();
        $connection = $resource->getConnection();
        $productIds = array_keys($documentData);

        $connection->query('SET SESSION group_concat_max_len = 1000000;');

        $select = $connection->select()
            ->from(['main_table' => $resource->getTableName('catalog_product_option')], ['product_id'])
            ->joinLeft(
                ['otv' => $resource->getTableName('catalog_product_option_type_value')],
                'main_table.option_id = otv.option_id',
                ['sku' => new \Zend_Db_Expr("GROUP_CONCAT(otv.`sku` SEPARATOR ' ')")]
            )
            ->joinLeft(
                ['ott' => $resource->getTableName('catalog_product_option_type_title')],
                'otv.option_type_id = ott.option_type_id',
                ['title' => new \Zend_Db_Expr("GROUP_CONCAT(ott.`title` SEPARATOR ' ')")]
            )
            ->where('main_table.product_id IN (?)', $productIds)
            ->group('product_id');

        foreach ($connection->fetchAll($select) as $row) {
            if (!isset($documentData[$row['product_id']]['_misc'])) {
                $documentData[$row['product_id']]['_misc'] = '';
            }

            foreach (['title', 'sku'] as $field) {
                 if (!empty($row[$field])) {
                       $documentData[$row['product_id']]['_misc'] .= ' ' . strip_tags($row[$field]);
                 }
            }
        }

        return $documentData;
    }

    private function addBundledOptions(array $documentData): array
    {
        if (!$this->getIndex()->getProperty('include_bundled')) {
            return $documentData;
        }

        $attributeIds = implode(',', $this->getSearchableAttributes());

        $resource   = $this->context->getResource();
        $connection = $resource->getConnection();
        $productIds = array_keys($documentData);

        $connection->query('SET SESSION group_concat_max_len = 1000000;');

        $select = $connection->select()
            ->from(
                ['main_table' => $resource->getTableName('catalog_product_entity')],
                ['sku' => new \Zend_Db_Expr("group_concat(main_table.sku SEPARATOR ' ')")]
            );
        $joinField = 'entity_id';

        // enterprise
        $tbl = $connection->describeTable($resource->getTableName('catalog_product_entity'));
        if (isset($tbl['row_id'])) {
            $joinField = 'row_id';
            $select
                ->joinLeft(
                    ['cpr' => $resource->getTableName('catalog_product_relation')],
                    'main_table.entity_id = cpr.child_id',
                    []
                )->joinLeft(
                    ['cpe' => $resource->getTableName('catalog_product_entity')],
                    'cpe.row_id = cpr.parent_id',
                    ['parent_id' => 'entity_id']
                )->where('cpe.entity_id IN (?)', $productIds);
        } else {
            $select
                ->joinLeft(
                    ['cpr' => $resource->getTableName('catalog_product_relation')],
                    'main_table.entity_id = cpr.child_id',
                    ['parent_id']
                )
                ->where('cpr.parent_id IN (?)', $productIds);
        }
        $select->joinLeft(
            ['product_varchar' => $resource->getTableName('catalog_product_entity_varchar')],
            'product_varchar.'. $joinField .' = cpr.child_id AND product_varchar.attribute_id IN ('. $attributeIds .')',
            ['varchar_value' => new \Zend_Db_Expr("group_concat(product_varchar.value SEPARATOR ' ')")]
        )->joinLeft(
            ['product_text' => $resource->getTableName('catalog_product_entity_text')],
            'product_text.'. $joinField .' = cpr.child_id AND product_text.attribute_id IN ('. $attributeIds .')',
            ['text_value' => new \Zend_Db_Expr("group_concat(product_text.value SEPARATOR ' ')")]
        )->joinLeft(
            ['product_decimal' => $resource->getTableName('catalog_product_entity_decimal')],
            'product_decimal.'. $joinField .' = cpr.child_id AND product_decimal.attribute_id IN ('. $attributeIds .')',
            ['decimal_value' => new \Zend_Db_Expr("group_concat(product_decimal.value SEPARATOR ' ')")]
        )->group('cpr.parent_id');

        foreach ($connection->fetchAll($select) as $row) {
            if (!isset($documentData[$row['parent_id']]['_misc'])) {
                $documentData[$row['parent_id']]['_misc'] = '';
            }
            if (is_array($documentData[$row['parent_id']]['_misc'])) {
                $documentData[$row['parent_id']]['_misc'] = implode(' ', strip_tags($documentData[$row['parent_id']]['_misc']));
            }

            $documentData[$row['parent_id']]['_misc'] .= $this->configProvider->applyLongTail($row['sku']);
            $childrenData = $row['sku'] .' '. $row['varchar_value'] .' '. $row['text_value'] .' '. $row['decimal_value'];
            $childrenData = strip_tags($childrenData);

            $documentData[$row['parent_id']]['_misc'] .= ' ' . $childrenData;
            $documentData[$row['parent_id']]['_misc'] .= ' ' . $this->configProvider->applyLongTail($childrenData);
        }

        return $documentData;
    }

    private function addProductIdData(array $documentData): array
    {
        if (!$this->getIndex()->getProperty('include_id')) {
            return $documentData;
        }

        foreach ($documentData as $entityId => $data) {
            if (!isset($data['_misc'])) {
                $data['_misc'] = '';
            }
            if (is_array($data['_misc'])) {
                $data['_misc'] = implode(' ', $data['_misc']);
            }

            $data['_misc'] .= ' ' . $entityId;

            $documentData[$entityId] = $data;
        }

        return $documentData;
    }

    private function getSearchableAttributes():array
    {
        if (empty($this->searchableAttributes)) {
            $resource   = $this->context->getResource();
            $connection = $resource->getConnection();
            $select = 'SELECT attribute_id FROM '. $resource->getTableName('catalog_eav_attribute') .' where is_searchable = 1';
            $this->searchableAttributes = array_keys($connection->fetchAssoc($select));
        }

        return $this->searchableAttributes;
    }
}
