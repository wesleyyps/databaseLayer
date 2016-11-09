<?php
namespace DatabaseLayer\Src\QueryHelpers;

use DatabaseLayer\Src\Connection;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

class QueryOptions
{
    const PARAM_TODATETIME = 'toDateTime';
    /**
     * @var int
     * Callback para ser executado sempre que rodar uma iteração em qualquer fetch
     */
    const CALLBACKFETCH_ALWAYS = 'callbackFetchAlways';

    private $limit = 0;
    private $offset = null;

    /**
     * @var PaginationJoin[]
     */
    private $joins = [];

    /**
     * @var array
     */
    private $resultsetParams = [];

    /**
     * @return PaginationJoin[]
     */
    public function getPaginationJoins()
    {
        return $this->joins;
    }

    /**
     * @param PaginationJoin $_join
     * @return $this
     */
    public function addPaginationJoin(PaginationJoin $_join)
    {
        $this->joins[] = $_join;
        return $this;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param null $_limit
     * @return $this
     */
    public function setLimit($_limit)
    {
        $validator = Validator::intVal()->min(0);
        try {
            $valid = $validator->assert($_limit);
        } catch(ExceptionInterface $_e) {
            $valid = false;
            Connection::errorHandler($_e);
        }
        if($valid) {
            $this->limit = intval($_limit);
        }
        return $this;
    }

    /**
     * @return null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param null $_offset
     * @return $this
     */
    public function setOffset($_offset)
    {
        $validator = Validator::intVal()->min(0);
        try {
            $valid = $validator->assert($_offset);
        } catch(ExceptionInterface $_e) {
            $valid = false;
            Connection::errorHandler($_e);
        }
        if($valid) {
            $this->offset = $_offset;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getResultsetParams()
    {
        return $this->resultsetParams;
    }

    /**
     * @param array $_resultsetParams
     * @return $this
     */
    public function setResultsetParams(array $_resultsetParams = [])
    {
        $this->resultsetParams = $_resultsetParams;

        return $this;
    }
}