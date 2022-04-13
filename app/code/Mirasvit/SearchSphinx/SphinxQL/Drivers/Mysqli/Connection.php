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



namespace Mirasvit\SearchSphinx\SphinxQL\Drivers\Mysqli;

use Mirasvit\SearchSphinx\SphinxQL\Exception\ConnectionException;
use Mirasvit\SearchSphinx\SphinxQL\Exception\DatabaseException;
use Mirasvit\SearchSphinx\SphinxQL\Exception\SphinxQLException;
use Mirasvit\SearchSphinx\SphinxQL\Drivers\ConnectionBase;

/**
 * Class Connection
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class Connection extends ConnectionBase
{
    /**
     * Internal Encoding
     *
     * @var string
     */
    protected $internal_encoding = null;

    /**
     * Disables any warning outputs returned on the \MySQLi connection with @ prefix.
     *
     * @var boolean
     */
    protected $silence_connection_warning = false;

    /**
     * Forces the \MySQLi connection to suppress all errors returned. This should only be used
     * when the production server is running with high error reporting settings.
     *
     * @param boolean $enable True if it should be enabled, false if it should be disabled
     */
    public function silenceConnectionWarning($enable = true)
    {
        $this->silence_connection_warning = $enable;
    }

    /**
     * Returns the internal encoding.
     *
     * @return string current multibyte internal encoding
     */
    public function getInternalEncoding()
    {
        return $this->internal_encoding;
    }

    /**
     * Establishes a connection to the Sphinx server with \MySQLi.
     *
     * @param boolean $suppress_error If the warnings on the connection should be suppressed
     *
     * @return boolean True if connected
     * @throws ConnectionException If a connection error was encountered
     */
    public function connect($suppress_error = false)
    {
        $data = $this->getParams();
        $conn = mysqli_init();

        if (!empty($data['options'])) {
            foreach ($data['options'] as $option => $value) {
                $conn->options($option, $value);
            }
        }

        if (!$suppress_error && !$this->silence_connection_warning) {
            $conn->real_connect($data['host'], null, null, null, (int)$data['port'], $data['socket']);
        } else {
            $conn->real_connect($data['host'], null, null, null, (int)$data['port'], $data['socket']);
        }

        if ($conn->connect_error) {
            throw new ConnectionException('Connection Error: [' . $conn->connect_errno . ']'
                . $conn->connect_error);
        }

        $conn->set_charset('utf8');
        $this->connection = $conn;
        $this->mbPush();

        return true;
    }

    /**
     * Pings the Sphinx server.
     *
     * @return boolean True if connected, false otherwise
     */
    public function ping()
    {
        try {
            $this->getConnection();
        } catch (ConnectionException $e) {
            $this->connect();
        }

        return $this->getConnection()->ping();
    }

    /**
     * Closes and unset the connection to the Sphinx server.
     */
    public function close()
    {
        $this->mbPop();
        $this->getConnection()->close();
        $this->connection = null;
    }

    /**
     * Performs a query on the Sphinx server.
     *
     * @param string $query The query string
     *
     * @return ResultSet The result array or number of rows affected
     * @throws DatabaseException If the executed query produced an error
     */
    public function query($query)
    {
        $this->ping();

        $resource = $this->getConnection()->query($query);

        if ($this->getConnection()->error) {
            throw new DatabaseException('[' . $this->getConnection()->errno . '] ' .
                $this->getConnection()->error . ' [ ' . $query . ']');
        }

        return new ResultSet($this, $resource);
    }

    /**
     * Performs multiple queries on the Sphinx server.
     *
     * @param array $queue Queue holding all of the queries to be executed
     *
     * @return MultiResultSet The result array
     * @throws DatabaseException In case a query throws an error
     * @throws SphinxQLException In case the array passed is empty
     */
    public function multiQuery(Array $queue)
    {
        $count = count($queue);

        if ($count === 0) {
            throw new SphinxQLException('The Queue is empty.');
        }

        $this->ping();

        // HHVM bug (2015/07/07, HipHop VM 3.8.0-dev (rel)): $mysqli->error and $mysqli->errno aren't set
        if (!$this->getConnection()->multi_query(implode(';', $queue))) {
            throw new DatabaseException('[' . $this->getConnection()->errno . '] ' .
                $this->getConnection()->error . ' [ ' . implode(';', $queue) . ']');
        };

        return new MultiResultSet($this);
//        return new MultiResultSet($this, $count);
    }

    /**
     * Escapes the input with \MySQLi::real_escape_string.
     * Based on FuelPHP's escaping function.
     *
     * @param string $value The string to escape
     *
     * @return string The escaped string
     * @throws DatabaseException If an error was encountered during server-side escape
     */
    public function escape($value)
    {
        if ($this->connection === null) {
            // this function is called a lot, making ping a significant cost if called every time
            $this->ping();
        }

        if (($value = $this->getConnection()->real_escape_string((string)$value)) === false) {
            // @codeCoverageIgnoreStart
            throw new DatabaseException($this->getConnection()->error, $this->getConnection()->errno);
            // @codeCoverageIgnoreEnd
        }

        return "'" . $value . "'";
    }

    /**
     * Enter UTF-8 multi-byte workaround mode.
     * @return Connection
     */
    public function mbPush()
    {
        $this->internal_encoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');

        return $this;
    }

    /**
     * Exit UTF-8 multi-byte workaround mode.
     * @return Connection
     */
    public function mbPop()
    {
        mb_internal_encoding($this->internal_encoding);
        $this->internal_encoding = null;

        return $this;
    }
}
