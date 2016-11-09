<?php
namespace DatabaseLayer\Src\QueryHelpers;

class PaginationJoin
{
    /**
     * @var string
     */
    private $columns;
    /**
     * @var string
     */
    private $joinStatement;
    /**
     * @var string
     */
    private $groupBy;

    /**
     * @return string
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getJoinStatement()
    {
        return $this->joinStatement;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }
}