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



namespace Mirasvit\ReportApi\Config\Loader;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\ReportApi\Config\Entity\Column;
use Mirasvit\ReportApi\Config\Entity\EavTable;
use Mirasvit\ReportApi\Config\Entity\Relation;
use Mirasvit\ReportApi\Config\Entity\Table;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\TableService;

class Map
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TableService
     */
    private $tableService;

    /**
     * Map constructor.
     * @param Schema $schema
     * @param ObjectManagerInterface $objectManager
     * @param TableService $tableService
     * @param Data $data
     */
    public function __construct(
        Schema $schema,
        ObjectManagerInterface $objectManager,
        TableService $tableService,
        Data $data
    ) {
        $this->schema        = $schema;
        $this->objectManager = $objectManager;
        $this->tableService  = $tableService;
        $this->data          = $data;
    }

    /**
     * @return $this
     */
    public function load()
    {
        $config = $this->data->get('config');

        if (is_array($config['table'])) {
            foreach ($config['table'] as $data) {
                $this->initTable($data);
            }
        }

        if (is_array($config['eavTable'])) {
            foreach ($config['eavTable'] as $data) {
                $this->initEavTable($data);
            }
        }

        foreach ($config['table'] as $data) {
            $this->initColumns($data);
        }

        foreach ($config['eavTable'] as $data) {
            $this->initColumns($data);
        }

        $this->initRelations($config);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function initTable($data)
    {
        $attributes             = $data[Converter::DATA_ATTRIBUTES_KEY];
        $attributes['label']    = isset($attributes['label']) ? $attributes['label'] : false;
        $attributes['group']    = isset($attributes['group']) ? $attributes['group'] : false;
        $attributes['isNative'] = true;

        $table = $this->objectManager->create(Table::class, $attributes);

        $this->schema->addTable($table);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function initEavTable($data)
    {
        $attributes             = $data[Converter::DATA_ATTRIBUTES_KEY];
        $attributes['label']    = isset($attributes['label']) ? $attributes['label'] : false;
        $attributes['group']    = isset($attributes['group']) ? $attributes['group'] : false;
        $attributes['isNative'] = true;

        $table = $this->objectManager->create(EavTable::class, $attributes);

        $this->schema->addTable($table);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function initColumns($data)
    {
        $table = $this->schema->getTable($data[Converter::DATA_ATTRIBUTES_KEY]['name']);

        $data['column'] = isset($data['column']) ? $data['column'] : [];

        $data['pk'] = isset($data['pk']) ? $data['pk'] : [];
        $data['fk'] = isset($data['fk']) ? $data['fk'] : [];

        $columns = $data['column'];

        foreach ($data['pk'] as $column) {
            $column[Converter::DATA_ATTRIBUTES_KEY]['type'] = 'pk';
            // rewrite PK column to match actual IDENTITY field from the table (entity_id => row_id)
            if (!in_array($column[Converter::DATA_ATTRIBUTES_KEY]['name'], array_keys($table->getFields()), true)) {
                foreach ($table->getFields() as $field) {
                    if ($field->isIdentity()) {
                        $column[Converter::DATA_ATTRIBUTES_KEY]['name'] = $field->getName();
                        break;
                    }
                }
            }

            $columns[] = $column;
        }

        foreach ($data['fk'] as $column) {
            $column[Converter::DATA_ATTRIBUTES_KEY]['type'] = 'fk';

            $columns[] = $column;
        }

        foreach ($columns as $data) {
            $data[Converter::DATA_ATTRIBUTES_KEY]['table'] = $table;

            if (isset($data[Converter::DATA_ATTRIBUTES_KEY]['tables'])) {
                $tables = explode(',', $data[Converter::DATA_ATTRIBUTES_KEY]['tables']);
                foreach ($tables as $idx => $tbl) {
                    $tables[$idx] = $this->schema->getTable($tbl);
                }

                $data[Converter::DATA_ATTRIBUTES_KEY]['tables'] = $tables;
            } else {
                $data[Converter::DATA_ATTRIBUTES_KEY]['tables'] = [];
            }

            $this->initColumn($data[Converter::DATA_ATTRIBUTES_KEY]);
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function initColumn($data)
    {
        if (isset($data['fields']) && $data['fields'] == $data['name']) {
            throw new \Exception("Fields should be different from name or not set: {$data['fields']}");
        }

        $name = $data['name'];

        $data['fields'] = !isset($data['fields']) ? [$data['name']] : explode(',', $data['fields']);
        $data['type']   = !isset($data['type']) ? 'string' : $data['type'];
        $data['label']  = !isset($data['label']) ? ucwords(str_replace('_', ' ', $name)) : $data['label'];

        $type = $this->objectManager->create(
            $this->schema->getType($data['type']),
            $data
        );

        foreach ($type->getAggregators() as $aggregatorName) {
            $aggregator = $this->objectManager->create(
                $this->schema->getAggregator($aggregatorName)
            );

            $columnData = [
                'name'       => $name . ($aggregatorName !== 'none' ? "__$aggregatorName" : ''),
                'type'       => $type,
                'aggregator' => $aggregator,
                'data'       => $data,
            ];

            $this->objectManager->create(Column::class, $columnData);
        }
    }

    /**
     * @param array $config
     *
     * @return void
     */
    private function initRelations($config)
    {
        // init explicitly declared relations
        $config['relation'] = isset($config['relation']) ? $config['relation'] : [];

        foreach ($config['relation'] as $relation) {
            $data = [
                'leftTable'  => $this->schema->getTable($relation[Converter::DATA_ARGUMENTS_KEY]['leftTable']),
                'rightTable' => $this->schema->getTable($relation[Converter::DATA_ARGUMENTS_KEY]['rightTable']),
                'leftField'  => null,
                'rightField' => null,
                'type'       => $relation[Converter::DATA_ATTRIBUTES_KEY]['type'],
                'condition'  => $relation[Converter::DATA_ARGUMENTS_KEY]['condition'],
            ];

            $relation = $this->objectManager->create(Relation::class, $data);

            $this->schema->addRelation($relation);
        }

        // init implicit relations between tables
        $tables = array_merge_recursive($config['table'], $config['eavTable']);

        foreach ($tables as $table) {
            if (!isset($table['fk'])) {
                continue;
            }
            foreach ($table['fk'] as $fk) {
                $leftTableName  = $table[Converter::DATA_ATTRIBUTES_KEY]['name'];
                $rightTableName = $fk[Converter::DATA_ATTRIBUTES_KEY]['table'];

                if (!$this->schema->hasTable($leftTableName)
                    || !$this->schema->hasTable($rightTableName)) {
                    continue;
                }

                $leftTable  = $this->schema->getTable($leftTableName);
                $rightTable = $this->schema->getTable($rightTableName);

                $leftFieldName = $fk[Converter::DATA_ATTRIBUTES_KEY]['name'];

                $leftField  = $leftTable->getField($leftFieldName);
                $rightField = $rightTable->getPkField();

                $type = isset($fk[Converter::DATA_ATTRIBUTES_KEY]['uniq']) ? 1 : 'n';

                $data = [
                    'leftTable'  => $this->schema->getTable($leftTableName),
                    'leftField'  => $leftField,
                    'rightTable' => $this->schema->getTable($rightTableName),
                    'rightField' => $rightField,
                    'type'       => '1' . $type,
                ];

                $relation = $this->objectManager->create(Relation::class, $data);

                $this->schema->addRelation($relation);
            }
        }
    }
}
