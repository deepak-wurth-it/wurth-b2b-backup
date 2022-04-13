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

use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\FieldInterface;
use Mirasvit\ReportApi\Api\Config\SelectInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\NamingService;

class Column implements ColumnInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TableInterface
     */
    private $table;

    /**
     * @var TableInterface[]
     */
    private $tables = [];

    /**
     * @var string
     */
    private $expression;

    /**
     * @var string
     */
    private $label;

    /**
     * @var FieldInterface[]
     */
    private $fieldsPool = [];

    /**
     * @var bool
     */
    private $isUnique = false;

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var AggregatorInterface
     */
    private $aggregator;

    /**
     * @var bool
     */
    private $isInternal = false;

    /**
     * Column constructor.
     *
     * @param Schema              $schema
     * @param TypeInterface       $type
     * @param AggregatorInterface $aggregator
     * @param string              $name
     * @param array               $data
     *
     * @throws \Exception
     */
    public function __construct(
        Schema $schema,
        TypeInterface $type,
        AggregatorInterface $aggregator,
        $name,
        $data = []
    ) {
        $this->name = $name;

        $this->type       = $type;
        $this->aggregator = $aggregator;

        $this->expression = isset($data['expr']) ? $data['expr'] : '%1';

        $this->label = $data['label'];

        if (isset($data['uniq'])) {
            $this->isUnique = true;
        }

        if (isset($data['internal'])) {
            $this->isInternal = true;
        }

        $this->table = $data['table'];
        $this->table->addColumn($this);

        if (isset($data['tables'])) {
            $this->tables = $data['tables'];
        }

        if (isset($data['fields'])) {
            foreach ($data['fields'] as $field) {
                // in case table has column named like 'v.value'
                try {
                    $this->fieldsPool[] = $this->table->getField($field);
                    continue;
                } catch (\Exception $e) {
                }

                $field = array_map('trim', explode('.', $field));
                if (count($field) == 1) {
                    $this->fieldsPool[] = $this->table->getField($field[0]);
                } else {
                    $table              = $schema->getTable($field[0]);
                    $this->fieldsPool[] = $table->getField($field[1]);
                }
            }
        }

        $this->label = NamingService::getLabel($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
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
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param TableInterface $table
     *
     * @return $this
     */
    public function setTable(TableInterface $table)
    {
        $this->table      = $table;
        $this->fieldsPool = [];
        $this->tables     = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregator()
    {
        return $this->aggregator;
    }

    /**
     * @param AggregatorInterface $aggregator
     *
     * @return $this
     */
    public function setAggregator(AggregatorInterface $aggregator)
    {
        $this->aggregator = $aggregator;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->isUnique || $this->getType()->getType() == TypeInterface::TYPE_PK;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isInternal()
    {
        return $this->isInternal;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fieldsPool = [];
        foreach ($fields as $field) {
            $this->fieldsPool[] = $this->table->getField($field);
        }

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
     * @param mixed $expr
     *
     * @return $this
     */
    public function setExpression($expr)
    {
        $this->expression = $expr;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toDbExpr()
    {
        $exr = $this->aggregator->getExpression();
        $exr = str_replace('%1', $this->expression, $exr);

        $idx = 1;
        foreach ($this->fieldsPool as $field) {
            $isTmp     = strrpos($field->getTable()->getName(), 'tmp') === 0;
            $fieldExpr = $field->getName() == 'entity_id' && (!$exr || $exr == '%1') && $isTmp
                ? $field->getName() . '__value'
                : $field->toDbExpr();

            $exr = str_replace('%' . $idx, $fieldExpr, $exr);
            $idx++;
        }

        return new \Zend_Db_Expr($exr);
    }

    /**
     * {@inheritdoc}
     */
    public function join(SelectInterface $select)
    {
        $isJoined = $select->joinTable($this->table);

        foreach ($this->tables as $tbl) {
            $isJoined = $select->joinTable($tbl) ? $isJoined : false;
        }

        return $isJoined;
    }

    /**
     * {@inheritdoc}
     */
    public function joinRight(SelectInterface $select)
    {
        $isJoined = $select->joinTable($this->table);

        foreach ($this->tables as $tbl) {
            $isJoined = $select->joinTable($tbl) ? $isJoined : false;
        }

        return $isJoined;
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterOnly(ReportInterface $report)
    {
        if (in_array($this->getIdentifier(), $report->getAvailableFilters(), true)
            && !in_array($this->getIdentifier(), $report->getDefaultColumns(), true)
            && !in_array($this->getIdentifier(), $report->getColumns(), true)
            && !in_array($this->getIdentifier(), $report->getDimensions(), true)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return "{$this->table->getName()}|{$this->name}";
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->getIdentifier()}";
    }
}
