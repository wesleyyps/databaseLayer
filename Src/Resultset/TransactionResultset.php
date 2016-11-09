<?php
namespace DatabaseLayer\Src\Resultset;

class TransactionResultset
{
    /**
     * @var boolean
     */
    private $data = null;
    /**
     * @var string
     */
    private $label = null;
    /**
     * @var array
     */
    private $codigos = null;
    /**
     * @var array
     */
    private $valoresGuardados = null;
    /**
     * @var string
     */
    private $consulta = null;
    /**
     * @var array
     */
    private $keepId = null;
    /**
     * @var array
     */
    private $keepIdVault = null;
    /**
     * @var array
     */
    private $errors = null;

    public function __construct($_dados) {
        if(isset($_dados['data'])) { $this->data = $_dados['data'] ; }
        if(isset($_dados['label'])) { $this->label = $_dados['label'] ; }
        if(isset($_dados['codigos'])) { $this->ids = $_dados['codigos'] ; }
        if(isset($_dados['valoresGuardados'])) { $this->keepValues = $_dados['valoresGuardados'] ; }
        if(isset($_dados['errors'])) { $this->errors = $_dados['errors'] ; }
        if(isset($_dados['consulta'])) { $this->query = $_dados['consulta'] ; }
        if(isset($_dados['keepId'])) { $this->keepId = $_dados['keepId'] ; }
        if(isset($_dados['keepIdVault'])) { $this->keepIdVault = $_dados['keepIdVault'] ; }
    }

    /**
     * @return boolean
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getCodigos()
    {
        return $this->codigos;
    }

    /**
     * @return array
     */
    public function getValoresGuardados()
    {
        return $this->valoresGuardados;
    }

    /**
     * @return string
     */
    public function getConsulta()
    {
        return $this->consulta;
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
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}