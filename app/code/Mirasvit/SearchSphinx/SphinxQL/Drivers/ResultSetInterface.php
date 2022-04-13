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



namespace Mirasvit\SearchSphinx\SphinxQL\Drivers;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
interface ResultSetInterface extends \ArrayAccess, \Iterator, \Countable
{
    /**
     * Stores all the result data in the object and frees the database results
     *
     * @return static
     */
    public function store();

    /**
     * Checks if the specified row exists
     *
     * @param int $row The number of the row to check on
     * @return bool True if the row exists, false otherwise
     */
    public function hasRow($row);

    /**
     * Moves the cursor to the specified row
     *
     * @param int $row The row to move the cursor to
     * @return static
     */
    public function toRow($row);

    /**
     * Checks if the next row exists
     *
     * @return bool True if the row exists, false otherwise
     */
    public function hasNextRow();

    /**
     * Moves the cursor to the next row
     *
     * @return static
     */
    public function toNextRow();

    /**
     * Returns the number of affected rows
     *
     * @return int
     */
    public function getAffectedRows();

    /**
     * Returns the number of rows in the result set
     *
     * @return int The number of rows in the result set
     */
    public function getCount();

    /**
     * Fetches all the rows as an array of associative arrays
     *
     * @return array An array of associative arrays
     */
    public function fetchAllAssoc();

    /**
     * Fetches all the rows as an array of indexed arrays
     *
     * @return array An array of indexed arrays
     */
    public function fetchAllNum();

    /**
     * Fetches all the rows the cursor points to as an associative array
     *
     * @return array An associative array representing the row
     */
    public function fetchAssoc();

    /**
     * Fetches all the rows the cursor points to as an indexed array
     *
     * @return array An indexed array representing the row
     */
    public function fetchNum();

    /**
     * Frees the database from the result
     *
     * @return static
     */
    public function freeResult();
}
