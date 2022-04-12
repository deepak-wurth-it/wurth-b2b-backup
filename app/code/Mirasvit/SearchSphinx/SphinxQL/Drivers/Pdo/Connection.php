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

use Mirasvit\SearchSphinx\SphinxQL\Drivers\ConnectionBase;
use Mirasvit\SearchSphinx\SphinxQL\Exception\ConnectionException;
use Mirasvit\SearchSphinx\SphinxQL\Exception\DatabaseException;
use Mirasvit\SearchSphinx\SphinxQL\Exception\SphinxQLException;

/**
 * Class PdoConnection
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class Connection extends ConnectionBase
{
    /**
     * @var bool
     */
    protected $silence_connection_warning = false;

    /**
     * @param bool $enable
     * @deprecated
     * not good
     */
    public function silenceConnectionWarning($enable = true)
    {
        $this->silence_connection_warning = $enable;
    }

    /**
     * close connection
     */
    public function close()
    {
        $this->connection = null;
    }

    /**
     * Performs a query on the Sphinx server.
     *
     * @param string $query The query string
     *
     * @return ResultSet The result array or number of rows affected
     * @throws DatabaseException
     */
    public function query($query)
    {
        $this->ping();

        $stm = $this->connection->prepare($query);

        try {
            $stm->execute();
        } catch (\PDOException $exception) {
            throw new DatabaseException($exception->getMessage() . ' [' . $query . ']');
        }

        return new ResultSet($stm);
    }

    /**
     * @param bool $suppress_error
     * @return bool
     * @throws ConnectionException
     */
    public function connect($suppress_error = false)
    {
        $params = $this->getParams();

        $dsn = 'mysql:';
        if (isset($params['host']) && $params['host'] != '') {
            $dsn .= 'host=' . $params['host'] . ';';
        }
        if (isset($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }
        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        if (isset($params['socket']) && $params['socket'] != '') {
            $dsn .= 'unix_socket=' . $params['socket'] . ';';
        }

        if (!$suppress_error && !$this->silence_connection_warning) {
            try {
                $con = new \Pdo($dsn);
            } catch (\PDOException $exception) {
                trigger_error('connection error', E_USER_WARNING);
            }
        } else {
            try {
                $con = new \Pdo($dsn);
            } catch (\PDOException $exception) {
                throw new ConnectionException($exception->getMessage());
            }
        }
        if (!isset($con)) {
            throw new ConnectionException('connection error');
        }
        $this->connection = $con;
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return true;
    }

    /**
     * @return bool
     * @throws ConnectionException
     */
    public function ping()
    {
        try {
            $this->getConnection();
        } catch (ConnectionException $e) {
            $this->connect();
        }

        return $this->connection !== null;
    }

    /**
     * @param array $queue
     * @return MultiResultSet
     * @throws DatabaseException
     * @throws SphinxQLException
     */
    public function multiQuery(Array $queue)
    {
        $this->ping();

        if (count($queue) === 0) {
            throw new SphinxQLException('The Queue is empty.');
        }

        $result = array();
        $count = 0;

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            try {
                $statement = $this->connection->query(implode(';', $queue));
            } catch (\PDOException $exception) {
                throw new DatabaseException($exception->getMessage() . ' [ ' . implode(';', $queue) . ']');
            }

            return new MultiResultSet($statement);
        } else {
            foreach ($queue as $sql) {
                try {
                    $statement = $this->connection->query($sql);
                } catch (\PDOException $exception) {
                    throw new DatabaseException($exception->getMessage() . ' [ ' . implode(';', $queue) . ']');
                }
                if ($statement->columnCount()) {
                    $set = new ResultSet($statement);
                    $rowset = $set->getStored();
                } else {
                    $rowset = $statement->rowCount();
                }

                $result[$count] = $rowset;
                $count++;
            }

            return new MultiResultSet($result);
        }
    }

    /**
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $this->ping();

        return $this->connection->quote($value);
    }
}
