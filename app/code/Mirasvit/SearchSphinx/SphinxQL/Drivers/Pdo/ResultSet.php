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



namespace Mirasvit\SearchSphinx\SphinxQL\Drivers\Pdo;

use Mirasvit\SearchSphinx\SphinxQL\Exception\ResultSetException;
use Mirasvit\SearchSphinx\SphinxQL\Drivers\ResultSetBase;
use PDO;
use PDOStatement;

/**
 * Class ResultSet
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class ResultSet extends ResultSetBase
{
    /**
     * @var \mysqli_result
     */
    protected $result;

    /**
     * @var null|array
     */
    protected $fields = array();

    /**
     * @var null|array
     */
    protected $stored = null;

    /**
     * @var null|array
     */
    protected $fetched = null;
    /**
     * @var PDOStatement
     */
    private $statement;

    /**
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;

        if ($this->statement->columnCount() > 0) {
            $this->num_rows = $this->statement->rowCount();

            for ($i = 0; $i < $this->statement->columnCount(); $i++) {
                $this->fields[] = $this->statement->getColumnMeta($i);
            }

            $this->store();

        } else {
            $this->affected_rows = $this->statement->rowCount();
            $this->store();
        }
    }

    /**
     * Store all the data in this object and free the mysqli object
     *
     * @return static $this
     */
    public function store()
    {
        if ($this->stored !== null) {
            return $this;
        }

        if ($this->statement->columnCount() > 0) {
            $this->stored = $this->statement->fetchAll(PDO::FETCH_NUM);
        } else {
            $this->stored = $this->affected_rows;
        }

        return $this;
    }

    /**
     * Returns the array as in version 0.9.x
     *
     * @return array|int|mixed
     */
    public function getStored()
    {
        if ($this->statement->columnCount() === 0) {
            return $this->getAffectedRows();
        }

        return $this->fetchAllAssoc();
    }

    /**
     * Moves the cursor to the selected row
     *
     * @param int $num The number of the row to move the cursor to
     * @return static
     * @throws ResultSetException If the row does not exist
     */
    public function toRow($num)
    {
        if (!$this->hasRow($num)) {
            throw new ResultSetException('The row does not exist.');
        }

        $this->current_row = $num;
        // $this->result->data_seek($num);
        // $this->fetched = $this->statement->fetch(PDO::FETCH_NUM);

        return $this;
    }

    /**
     * Moves the cursor to the next row
     *
     * @return static $this
     * @throws ResultSetException If the next row does not exist
     */
    public function toNextRow()
    {
        if (!$this->hasNextRow()) {
            throw new ResultSetException('The next row does not exist.');
        }

        if ($this->current_row === null) {
            $this->current_row = 0;
        } else {
            $this->current_row++;
        }

        $this->fetched = $this->statement->fetch(PDO::FETCH_NUM);

        return $this;
    }

    /**
     * Fetches all the rows as an array of associative arrays
     *
     * @return array|mixed
     */
    public function fetchAllAssoc()
    {
        if ($this->stored !== null) {
            $result = array();
            foreach ($this->stored as $row_key => $row_value) {
                foreach ($row_value as $col_key => $col_value) {
                    $result[$row_key][$this->fields[$col_key]['name']] = $col_value;
                }
            }

            return $result;
        }

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all the rows as an array of indexed arrays
     *
     * @return array|mixed|null
     */
    public function fetchAllNum()
    {
        if ($this->stored !== null) {
            return $this->stored;
        }

        return $this->statement->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fetches a row as an associative array
     *
     * @return array
     */
    public function fetchAssoc()
    {
        if ($this->stored) {
            $row = $this->stored[$this->current_row];
        } else {
            $row = $this->fetched;
        }

        $result = array();
        foreach ($row as $col_key => $col_value) {
            $result[$this->fields[$col_key]['name']] = $col_value;
        }

        return $result;
    }

    /**
     * Fetches a row as an indexed array
     *
     * @return array|null
     */
    public function fetchNum()
    {
        if ($this->stored) {
            return $this->stored[$this->current_row];
        } else {
            return $this->fetched;
        }
    }

    /**
     * Get the result object returned by PHP's MySQLi
     *
     * @return \mysqli_result
     *
     * @codeCoverageIgnore
     */
    public function getResultObject()
    {
        return $this->result;
    }

    /**
     * Frees the memory from the result
     * Call it after you're done with a result set
     *
     * @return static
     */
    public function freeResult()
    {
        $this->statement->closeCursor();

        return $this;
    }
}
