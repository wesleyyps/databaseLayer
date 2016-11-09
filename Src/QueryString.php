<?php
namespace Src;

use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\QueryHelpers\QueryOptions;
use DatabaseLayer\Src\Resultset\QueryResultset;

class SqlString
{
    /**
     * @var string
     */
    private $queryString;
    /**
     * @var string
     */
    private $connection = 'default';

    /**
     * @param string $_queryString
     * @param string $_connection
     */
    public function __construct($_queryString, $_connection = 'default')
    {
        if(!is_null($_connection) && trim($_connection) != '') {
            $this->connection = $_connection;
        }
        $this->queryString = $_queryString;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->queryString;
    }

    /**
     * @return QueryResultset
     */
    public function findOne()
    {
        Connection::open($this->connection);
        return Connection::query(
            $this->queryString,
            (new QueryOptions())
                ->setLimit(1)
                ->setOffset(0)
        );
    }

    /**
     * @param QueryOptions $_options
     * @return QueryResultset
     */
    public function findAll(QueryOptions $_options)
    {
        Connection::open($this->connection);
        $res = Connection::query($this->queryString, $_options);
        return $res;
    }
}