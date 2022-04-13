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

use Mirasvit\ReportApi\Api\Config\FieldInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;

class Relation implements RelationInterface
{
    /**
     * @var TableInterface
     */
    private $leftTable;

    /**
     * @var FieldInterface
     */
    private $leftField;

    /**
     * @var TableInterface
     */
    private $rightTable;

    /**
     * @var FieldInterface
     */
    private $rightField;

    /**
     * @var string
     */
    private $condition;

    /**
     * @var string
     */
    private $type;

    /**
     * Relation constructor.
     * @param TableInterface $leftTable
     * @param mixed $leftField
     * @param TableInterface $rightTable
     * @param mixed $rightField
     * @param string $type
     * @param string $condition
     */
    public function __construct(
        TableInterface $leftTable,
        $leftField,
        TableInterface $rightTable,
        $rightField,
        $type,
        $condition = ''
    ) {
        $this->leftTable = $leftTable;
        $this->leftField = $leftField;

        $this->rightTable = $rightTable;
        $this->rightField = $rightField;

        $this->condition = $condition;

        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeftTable()
    {
        return $this->leftTable;
    }

    /**
     * @return FieldInterface
     */
    public function getLeftField()
    {
        return $this->leftField;
    }

    /**
     * {@inheritdoc}
     */
    public function getRightTable()
    {
        return $this->rightTable;
    }

    /**
     * @return FieldInterface
     */
    public function getRightField()
    {
        return $this->rightField;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(TableInterface $table)
    {
        if ($this->rightTable === $table) {
            return $this->type[1];
        }

        return $this->type[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getOppositeTable(TableInterface $table)
    {
        if ($this->leftTable === $table) {
            return $this->rightTable;
        } elseif ($this->rightTable === $table) {
            return $this->leftTable;
        }

        return false;
    }

    /**
     * @param FieldInterface $field
     * @return bool|FieldInterface
     */
    public function getOppositeField(FieldInterface $field)
    {
        if ($this->leftField === $field) {
            return $this->rightField;
        } elseif ($this->rightField === $field) {
            return $this->leftField;
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->type}\t{$this->leftTable->getName()}\t{$this->rightTable->getName()}\t\t{$this->getCondition()}";
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        $condition = '';

        if ($this->leftField && $this->rightField) {
            $right     = $this->rightField instanceof FieldInterface ? $this->rightField->toDbExpr() : $this->rightField;
            $condition = $this->leftField->toDbExpr() . ' = ' . $right;
        }

        if ($this->condition) {
            if ($condition) {
                $condition .= ' AND ' . $this->condition;
            } else {
                $condition .= $this->condition;
            }

            $condition = str_replace('%1', $this->leftTable->getName(), $condition);
            $condition = str_replace('%2', $this->rightTable->getName(), $condition);
        }

        //

        return $condition;
    }
}
