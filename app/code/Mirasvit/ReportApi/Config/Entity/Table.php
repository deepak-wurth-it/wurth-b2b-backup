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

use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\FieldInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Service\TableService;

class Table implements TableInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var bool
     */
    protected $isNative;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var FieldInterface[]
     */
    protected $fieldsPool = [];

    /**
     * @var ColumnInterface[]
     */
    protected $columnsPool = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var TableService
     */
    private $tableService;

    /**
     * @var bool
     */
    private $isTmp = false;

    /**
     * Table constructor.
     * @param TableService $tableService
     * @param FieldFactory $fieldFactory
     * @param string $name
     * @param mixed $label
     * @param bool $isNative
     * @param null $group
     * @param string $connection
     */
    public function __construct(
        TableService $tableService,
        FieldFactory $fieldFactory,
        $name,
        $label,
        $isNative = false,
        $group = null,
        $connection = 'default'
    ) {
        $this->name           = $name;
        $this->label          = $label;
        $this->isNative       = $isNative;
        $this->group          = $group;
        $this->connectionName = $connection;

        $this->fieldFactory = $fieldFactory;
        $this->tableService = $tableService;

        $this->initFields();
    }

    /**
     * @return void
     */
    private function initFields()
    {
        $fields = $this->tableService->describeTable($this);

        foreach ($fields as $fieldName => $info) {
            $field = $this->fieldFactory->create([
                'table'    => $this,
                'name'     => $fieldName,
                'identity' => $info['IDENTITY'] ? true : false,
            ]);

            $this->fieldsPool[$field->getName()] = $field;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function isNative()
    {
        return $this->isNative;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($name)
    {
        if (isset($this->columnsPool[$name])) {
            return $this->columnsPool[$name];
        } else {
            throw new \Exception(__('Undefined column "%1"', $name));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->columnsPool;
    }

    /**
     * @return FieldInterface
     * @throws \Exception
     */
    public function getPkField()
    {
        # exception
        if ($this->getName() == 'catalog_product_entity' || $this->getName() == 'catalog_category_entity') {
            foreach ($this->fieldsPool as $field) {
                if ($field->getName() == 'entity_id') {
                    return $field;
                }
            }
        }

        foreach ($this->fieldsPool as $field) {
            if ($field->isIdentity()) {
                return $field;
            }
        }

        throw new \Exception("Can't find primary field to table {$this->name}");
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn(ColumnInterface $column)
    {
        $this->columnsPool[$column->getName()] = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->fieldsPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($name)
    {
        if (key_exists($name, $this->fieldsPool)) {
            return $this->fieldsPool[$name];
        }

        throw new \Exception(__("Field %1 does not exist in table %2", $name, $this->getName()));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->name}";
    }

    /**
     * {@inheritdoc}
     */
    public function isTmp()
    {
        return $this->isTmp;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsTmp($isTmp = true)
    {
        $this->isTmp = $isTmp;

        return $this;
    }
}
