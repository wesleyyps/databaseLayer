<?php
namespace DatabaseLayer\Src\QueryHelpers;

class TransactionOptions
{
    /**
     * @var int
     */
    private $attempts = 3;
    /**
     * @var string
     */
    private $delay = '00:00:00.500';
    /**
     * @var array
     */
    private $keepId = null;
    /**
     * @var array
     */
    private $keepIdVault = null;
    /**
     * @var bool
     */
    private $debug = false;
    /**
     * @var bool
     */
    private $logAllErrors = true;
    /**
     * @var bool
     */
    private $cmdPrintPrct = false;

    private $auditTable = null;

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return boolean
     */
    public function isCmdPrintPrct()
    {
        return $this->cmdPrintPrct;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @return string
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @return array
     */
    public function getKeepId()
    {
        return $this->keepId;
    }

    /**
     * @return array
     */
    public function getKeepIdVault()
    {
        return $this->keepIdVault;
    }

    /**
     * @return boolean
     */
    public function isLogAllErrors()
    {
        return $this->logAllErrors;
    }

    /**
     * @return string
     */
    public function getAuditTable()
    {
        return $this->auditTable;
    }

    /**
     * @param int $_attempts
     * @return $this
     */
    public function setAttempts($_attempts)
    {
        $this->attempts = $_attempts;
        return $this;
    }

    /**
     * @param null $_auditTable
     * @return $this
     */
    public function setAuditTable($_auditTable)
    {
        $this->auditTable = $_auditTable;
        return $this;
    }

    /**
     * @param boolean $_cmdPrintPrct
     * @return $this
     */
    public function setCmdPrintPrct($_cmdPrintPrct)
    {
        $this->cmdPrintPrct = $_cmdPrintPrct;
        return $this;
    }

    /**
     * @param boolean $_debug
     * @return $this
     */
    public function setDebug($_debug)
    {
        $this->debug = $_debug;
        return $this;
    }

    /**
     * @param string $_delay
     * @return $this
     */
    public function setDelay($_delay)
    {
        $this->delay = $_delay;
        return $this;
    }

    /**
     * @param array $_keepId
     * @return $this
     */
    public function setKeepId($_keepId)
    {
        $this->keepId = $_keepId;
        return $this;
    }

    /**
     * @param array $_keepIdVault
     * @return $this
     */
    public function setKeepIdVault($_keepIdVault)
    {
        $this->keepIdVault = $_keepIdVault;
        return $this;
    }

    /**
     * @param boolean $_logAllErrors
     * @return $this
     */
    public function setLogAllErrors($_logAllErrors)
    {
        $this->logAllErrors = $_logAllErrors;
        return $this;
    }
}