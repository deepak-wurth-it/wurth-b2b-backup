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


use Mirasvit\SearchSphinx\SphinxQL\Drivers\MultiResultSetBase;
use Mirasvit\SearchSphinx\SphinxQL\Exception\DatabaseException;
use PDOStatement;

/**
 * Class MultiResultSet
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class MultiResultSet extends MultiResultSetBase
{
    /**
     * @var PDOStatement
     */
    public $statement;

    /**
     * @param PDOStatement|array|int $statement
     */
    public function __construct($statement)
    {
        if ($statement instanceof PDOStatement) {
            $this->statement = $statement;
        } else {
            $this->stored = $statement; // for php < 5.4.0
        }
    }

    /**
     * @return $this|MultiResultSetBase
     * @throws DatabaseException
     */
    public function store(): mixed
    {
        if ($this->stored !== null) {
            return $this;
        }

        // don't let users mix storage and pdo cursors
        if ($this->cursor !== null) {
            throw new DatabaseException('The MultiResultSet is using the pdo cursors, store() can\'t fetch all the data');
        }

        $store = array();
        while ($set = $this->getNext()) {
            // this relies on stored being null!
            $store[] = $set->store();
        }

        $this->cursor = null;

        // if we write the array straight to $this->stored it won't be null anymore and functions relying on null will break
        $this->stored = $store;

        return $this;
    }

    /**
     * @return bool|false|ResultSet|\Mirasvit\SearchSphinx\SphinxQL\Drivers\ResultSetInterface|mixed
     */
    public function getNext(): mixed
    {
        if ($this->stored !== null) {
            if ($this->cursor === null) {
                $this->cursor = 0;
            } else {
                $this->cursor++;
            }

            if ($this->cursor >= count($this->stored)) {
                return false;
            }

            return $this->stored[$this->cursor];
        } else {
            // the first result is always already loaded
            if ($this->cursor === null) {
                $this->cursor = 0;
            } else {
                $this->cursor++;
                $res = $this->statement->nextRowset();
                if (!$res) {
                    return false;
                }
            }

            return new ResultSet($this->statement);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        $this->store();

        return $this->stored[(int)$this->cursor];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        $this->store();

        return $this->cursor >= 0 && $this->cursor < count($this->stored);
    }
}
