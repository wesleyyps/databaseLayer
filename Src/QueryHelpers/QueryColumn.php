<?php
namespace DatabaseLayer\Src\QueryHelpers;

class QueryColumn {
    /**
     * @var bool
     */
    private $status;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $maxLength;
    /**
     * @var string
     */
    private $source;
    /**
     * @var bool
     */
    private $numeric;
    /**
     * @var string
     */
    private $type;

    /**
     * @param bool $_status
     * @param string $_name
     * @param int $_maxLength
     * @param string $_source
     * @param bool $_numeric
     * @param string $_type
     */
    public function __construct($_status, $_name, $_maxLength, $_source, $_numeric, $_type) {
        $this->status = (bool)$_status;
        $this->name = $_name;
        $this->maxLength = $_maxLength;
        $this->source = $_source;
        $this->numeric = (bool)$_numeric;
        $this->type = $_type;
    }

    /**
     * Indica se as informações do campo foram encontradas e preenchidas
     * @return bool
     */
    public function getStatus() {
        return $this->status;
    }
    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getNumeric()
    {
        return $this->numeric;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}