<?php
namespace DatabaseLayer\Src\Resultset;

use DatabaseLayer\Src\Engine\EngineInterface;
use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\Engine\SQLSRVEngine;
use DatabaseLayer\Src\Helpers\DateTime;
use DatabaseLayer\Src\QueryHelpers\QueryColumn;
use DatabaseLayer\Src\QueryHelpers\QueryOptions;

class QueryResultset
{
    /**
     * Resource da consulta
     * @var resource
     */
    private $resource = null;
    /**
     * Informação de erro
     * @var string
     */
    private $errorInfo = null;
    /**
     * Configurações da conexão
     * @var array
     */
    private $attributes;
    /**
     * Engine do banco de dados
     * @var EngineInterface
     */
    private $engine;
    /**
     * Indica se houve erro ou não
     * @var boolean
     */
    private $status;
    /**
     * Quantidade de linhas afetadas / retornadas
     * @var int
     */
    private $rows;
    /**
     * Quantidade total de registros retornados em uma consulta paginada (desconsiderando o limit e o offset)
     * @var int
     */
    private $fullRowCount;
    /**
     * Quantidade de colunas da consulta
     * @var int
     */
    private $cols;
    /**
     * Id gerado na ultima operação de inserção
     * @var int
     */
    private $insertId;
    /**
     * Informações das colunas retornadas em uma consulta
     * @var QueryColumn[]
     */
    private $columnInfo = [];
    private $dateTimeColumns = [];

    public $queryString = '';
    public $queryTime = null;
    public $executionTime = null;

    /**
     * @param EngineInterface $_engine
     * @param resource $_resource
     * @param boolean $_status
     * @param int $_rows
     * @param int $_cols
     * @param int $_fullRowCount
     * @param int $_insertId
     * @param array $_attr
     * @return QueryResultset
     */
    public function __construct(EngineInterface $_engine, $_resource,$_status,$_rows,$_cols,$_fullRowCount,$_insertId,$_attr= [])
    {
        $this->engine		= $_engine;
        $this->resource		= $_resource;
        $this->status		= $_status;
        $this->attributes	= $_attr;
        $this->rows			= $_rows;
        $this->fullRowCount	= $_fullRowCount;
        $this->cols			= $_cols;
        $this->insertId		= $_insertId;
    }

    /**
     * Retorna todos os registros de acordo com o fetchStyle
     *
     * @param int $_fetchStyle
     * - Estilo de retorno dos dados. Pode assumir os seguintes valores:
     * + null|'' : Assume o padrão de retorno;
     * + FETCH_ASSOC(1) - Retorna um array associativo com o nome dos campos;
     * + FETCH_NUM(2) - Retorna um array associativo com o número da coluna na consulta;
     * + FETCH_BOTH(3) - Retorna um array associativo contendo o nome e o número da coluna;
     * + FETCH_OBJ(4) - Retorna um objeto contendo as colunas como as respectivas propriedades;
     * + FETCH_CLASS(5) - Retorna um objeto contendo as colunas como as respectivas propriedades, podendo informar uma classe customizada para receber a informação em string($_optArg);
     * + FETCH_COLUMN(6) - Retorna somente a coluna informada em int($_optArg);
     * + FETCH_SERIALIZE(7) - Retorna um array com os dados da linha serializados;
     * + FETCH_JSON(8) - Retorna um array com os dados da linha em json;
     * + FETCH_SERIALIZE_ALL(9) - Retorna os dados da consulta no formato de um array serializado;
     * + FETCH_JSON_ALL(10) - Retorna os dados da consulta no formato de um array json;
     * @param mixed $_optArg
     * - Utilizado quando o $_fetchStyle for FETCH_CLASS / FETCH_COLUMN, recebendo respectivamente o nome da classe ou o número da coluna;
     * - Nos outros casos, $_optArg pode ser uma função anônima, para tratar os dados da linha.
     * A função fetchAll recebe dois parametros, o primeiro que é o array com os dados da linha, e o segundo que é o número da linha no ponteiro interno.
     * No caso da função fetch, o segundo parâmetro não existe, pois a mesma só retorna uma linha de cada vez;
     * @param array $_ctorArgs
     * - Utilizado quando o $_fetchStyle for FETCH_CLASS, recebendo parametros que a classe possa receber no construtor da mesma;
     *
     * @return array
     * - Array das linhas do resultado da consulta. Caso a consulta tenha falhado retorna um array vazio;
     */
    public function fetchAll($_fetchStyle = null, $_optArg = null, array $_ctorArgs = [])
    {
        $res = [];
        if(is_resource($this->resource)) {
            $dbTimezone = new \DateTimeZone($this->engine->getTimezone());

            $toDateTime = array_merge(
                (isset($_ctorArgs[QueryOptions::PARAM_TODATETIME])
                    ? $_ctorArgs[QueryOptions::PARAM_TODATETIME]
                    : []),
                (isset($this->attributes[QueryOptions::PARAM_TODATETIME])
                    ? $this->attributes[QueryOptions::PARAM_TODATETIME]
                    : [])
            );
            $checkToDT = count($toDateTime) > 0;

            switch(trim($_fetchStyle))
            {
                case null:
                case '':
                case Connection::FETCH_BOTH:
                case Connection::FETCH_NUM:
                case Connection::FETCH_ASSOC:
                    $resultType = (($_fetchStyle == Connection::FETCH_BOTH) ? null : (($_fetchStyle == Connection::FETCH_NUM) ? Connection::FETCH_NUM : Connection::FETCH_ASSOC));
                    $i = 0;
                    /** @noinspection PhpAssignmentInConditionInspection */
                    while($reg = $this->engine->fetchArray($this->resource,$resultType)) {
                        if($this->engine->isConvertionToUtf8Needed() && is_array($reg) > 0) {
                            array_walk($reg, [$this,'utf8EncodeArrayItem']);
                        }

                        if($checkToDT) {
                            $reg = self::convertDate($toDateTime, $dbTimezone, $reg);
                        }

                        if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                            $reg = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($reg, $i);
                        }
                        if(is_callable($_optArg)) {
                            $reg = $_optArg($reg, $i);
                        }
                        $res[] = $reg;
                        $i++;
                    }
                break;
                case Connection::FETCH_OBJ:
                    $i = 0;
                    /** @noinspection PhpAssignmentInConditionInspection */
                    while($reg = $this->engine->fetchObject($this->resource)) {
                        if($this->engine->isConvertionToUtf8Needed() && is_object($reg) > 0) {
                            array_walk($reg, [$this,'utf8EncodeArrayItem']);
                        }
                        if($checkToDT) {
                            $reg = self::convertDate($toDateTime, $dbTimezone, $reg);
                        }
                        if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                            $reg = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($reg, $i);
                        }
                        if(is_callable($_optArg)) {
                            $reg = $_optArg($reg, $i);
                        }
                        $res[] = $reg;
                        $i++;
                    }
                break;
                case Connection::FETCH_CLASS:
                    $rows = self::rowCount();
                    for($i = 0; $i < $rows; $i++) {
                        $res[] = self::toObject($_optArg,$_ctorArgs);
                    }
                break;
                case Connection::FETCH_COLUMN:
                    $rows = self::rowCount();
                    for($i = 0; $i < $rows; $i++) {
                        $res[] = self::toColumn($_optArg);
                    }
                break;
            }
        }
        return $res;
    }

    /**
     * Retorna a próxima linha do resultado no formato de objeto
     *
     * @param string $_className
     * - Nome da classe que recebera os dados. Caso não seja informado retorna um stdObject;
     * @param array $_ctorArgs
     * - Array de argumentos para o construtor da classe customizada;
     *
     * @return mixed
     * - Caso ocorra uma falha retorna FALSE, caso contrário retorna um objeto
     */
    public function fetchObject($_className = null, array $_ctorArgs = [])
    {
        $class = false;

        if(is_resource($this->resource))
        {
            $class = self::toObject($_className,$_ctorArgs);
        }

        return $class;
    }

    /**
     * Método para transformar o próximo registro em um objeto
     *
     * @param string $_className
     * - Nome da classe que recebera os dados. Caso não seja informado retorna um stdObject;
     * @param array $_ctorArgs
     * - Array de argumentos para o construtor da classe customizada;
     *
     * @return Object
     */
    private function toObject($_className = null, array $_ctorArgs = [])
    {
        if(!empty($_className)) {
            if(is_object($_className)) {
                $class = $_className;
            } else {
                $rc = new \ReflectionClass($_className);
                $class = $rc->newInstanceArgs($_ctorArgs);
            }

            $reg = $this->engine->fetchArray($this->resource, Connection::FETCH_ASSOC);

            if(method_exists($class, 'preencheObjeto')) {
                $reg2 = [];
                foreach ($reg as $key => $val) {
                    if ($this->engine->isConvertionToUtf8Needed()) {
                        $val = utf8_encode($val);
                    }
                    $reg2[$key] = $val;
                }
                if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                    $reg2 = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($reg2);
                }
                call_user_func_array([$class, 'preencheObjeto'], $reg2);
            } else {
                foreach ($reg as $key => $val) {
                    if ($this->engine->isConvertionToUtf8Needed()) {
                        $val = utf8_encode($val);
                    }
                    $class->$key = $val;
                }
            }
        } else {
            $values = $this->engine->fetchObject($this->resource);
            if($this->engine->isConvertionToUtf8Needed() && is_object($values)) {
                array_walk($values, [$this,'utf8EncodeArrayItem']);
            }
            if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                $values = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($values);
            }
            $class = $values;
        }
        return $class;
    }

    /**
     * Carrega os dados em um objeto pré-existente. Caso o objeto não possua o método preencheObjeto, este método tenta preencher o objeto através dos métodos set do mesmo, utilizando o nome do campo na consulta para compor o nome do método set, portanto o nome do campo precisa bater com o nome do método (exceto pelo 'set' precedendo)
     * @param array $_values Valores recebidos do banco de dados
     * @param mixed $_object Qualquer CLASSE existente no sistema
     * @return bool
     */
    private function loadObject($_values, $_object)
    {
        $res = false;

        if(method_exists($_object, 'preencheObjeto')) {
            $res = !(call_user_func_array([$_object, 'preencheObjeto'], $_values) === false);
        } else {
            foreach($_values as $key => $val) {
                $setter = 'set'.ucfirst(trim($key));
                if(method_exists($_object, $setter)) {
                    $_object->$setter($val);

                    $res = true;
                }
            }
        }

        return $res;
    }

    /**
     * Retorna uma coluna da próxima linha do resultado
     *
     * @param int $_columnNumber
     *
     * @return mixed
     * - Caso ocorra um erro retorna false, caso contrário retorna o dado da coluna;
     */
    public function fetchColumn($_columnNumber = null)
    {
        $res = false;

        if(is_resource($this->resource)) {
            self::toColumn($_columnNumber);
        }

        return $res;
    }

    /**
     * Método para retornar uma coluna do próximo registro
     *
     * @param int $_columnNumber
     *
     * @return mixed
     * - O valor da coluna;
     */
    private function toColumn($_columnNumber)
    {
        if(empty($_columnNumber)) {
            $_columnNumber = 0;
        }

        $reg = $this->engine->fetchArray($this->resource,Connection::FETCH_NUM);
        $res = $reg[$_columnNumber];

        return $res;
    }

    /**
     * Retorna o próximo registro
     *
     * @param int $_fetchStyle
     * @param mixed $_optArg
     * @param array $_ctorArgs
     * @see SqlStatement::fetchAll()
     *
     * @return mixed
     * - Caso ocorra algum erro retorna false, caso contrário retorna os dados no formato selecionado em $_fetchStyle;
     */
    public function fetch($_fetchStyle = null, $_optArg = null, array $_ctorArgs = [])
    {
        $res = false;

        if(is_resource($this->resource)) {
            $dbTimezone = new \DateTimeZone($this->engine->getTimezone());

            $toDateTime = array_merge(
                (isset($_ctorArgs[QueryOptions::PARAM_TODATETIME])
                    ? $_ctorArgs[QueryOptions::PARAM_TODATETIME]
                    : []),
                (isset($this->attributes[QueryOptions::PARAM_TODATETIME])
                    ? $this->attributes[QueryOptions::PARAM_TODATETIME]
                    : [])
            );
            $checkToDT = count($toDateTime) > 0;

            switch(trim($_fetchStyle)) {
                case null:
                case '':
                case Connection::FETCH_BOTH:
                case Connection::FETCH_NUM:
                case Connection::FETCH_ASSOC:
                    $resultType = (($_fetchStyle == Connection::FETCH_BOTH) ? null : (($_fetchStyle == Connection::FETCH_NUM) ? Connection::FETCH_NUM : Connection::FETCH_ASSOC));

                    $values = $this->engine->fetchArray($this->resource,$resultType);
                    if($this->engine->isConvertionToUtf8Needed() && is_array($values)) {
                        array_walk($values, [$this,'utf8EncodeArrayItem']);
                    }
                    if($checkToDT) {
                        $values = self::convertDate($toDateTime, $dbTimezone, $values);
                    }
                    if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                        $values = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($values);
                    }
                    if(is_callable($_optArg)) {
                        $values = $_optArg($values);
                    }
                    $res = $values;
                break;
                case Connection::FETCH_OBJ:
                    $values = $this->engine->fetchObject($this->resource);
                    if($this->engine->isConvertionToUtf8Needed() && is_object($values) > 0) {
                        array_walk($values, [$this,'utf8EncodeArrayItem']);
                    }
                    if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                        $values = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($values);
                    }
                    if($checkToDT) {
                        $values = self::convertDate($toDateTime, $dbTimezone, $values);
                    }
                    if(is_callable($_optArg)) {
                        $values = $_optArg($values);
                    }
                    $res = $values;
                break;
                case Connection::FETCH_CLASS:
                    $res = self::toObject($_optArg,$_ctorArgs);
                break;
                case Connection::FETCH_COLUMN:
                    $res = self::toColumn($_optArg);
                break;
                case Connection::FETCH_TOCLASS:
                    $values = $this->engine->fetchArray($this->resource,Connection::FETCH_ASSOC);
                    if($this->engine->isConvertionToUtf8Needed() && is_array($values)) {
                        array_walk($values, [$this,'utf8EncodeArrayItem']);
                    }
                    if($checkToDT) {
                        $values = self::convertDate($toDateTime, $dbTimezone, $values);
                    }
                    if(isset($this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS])) {
                        $values = $this->attributes[QueryOptions::CALLBACKFETCH_ALWAYS]($values);
                    }
                    if(is_callable($_ctorArgs)) {
                        $values = $_ctorArgs($values);
                    }
                    $res = self::loadObject($values, $_optArg);
                break;
            }
        }

        return $res;
    }

    public function freeStatement() {
        $this->engine->freeResult($this->resource);
    }

    /**
     * Retorna a quantidade de colunas da consulta
     *
     * @return int
     * - Caso seja executado um select, retorna a quantidade de colunas na consulta, caso contrário
     * retorna -1;
     */
    public function columnCount()
    {
        return $this->cols;
    }

    /**
     * Retorna a quantidade de linhas afetadas / retornadas
     *
     * @return int
     * - Caso seja executado um select, retorna a quantidade de linhas no resource da consulta. Caso seja
     * executado um insert ou update, ou qualquer operação que altere os registros da tabela, retorna a
     * quantidade de registros afetados. Para qualquer outra possibilidade retorna -1;
     */
    public function rowCount()
    {
        return $this->rows;
    }

    /**
     * Retorna o total de registros filtrados em uma consulta paginada
     *
     * @return int
     * - Caso seja executado um select informando os parametros limit e offset, retorna a quantidade
     * total de registros que a consulta retornaria sem paginação. Para outras possibilidades retorna -1;
     */
    public function fullRowCount()
    {
        return $this->fullRowCount;
    }

    /**
     * Retorna a lista com todas as colunas retornadas por uma instrução de consulta
     * @param callable $_function [optional]
     * @return QueryColumn[]
     */
    public function getColumnList(\Closure $_function = null) {
        if(count($this->columnInfo) == 0) {
            if($this->engine instanceof SQLSRVEngine) {

            }
            for($i = 0; $i < self::columnCount(); $i++) {
                $column = $this->engine->fetchField($this->resource, $i);
                if(is_callable($_function)) {
                    $column = $_function($column);
                }
                $this->columnInfo[$column['name']] = new QueryColumn($column['status'], $column['name'], $column['max_length'], $column['column_source'], $column['numeric'], $column['type']);
                if($column['type'] == 'datetime') {
                    $this->dateTimeColumns[] = $column['name'];
                }
            }
        }

        return $this->columnInfo;
    }

    /**
     * Retorna o id do último registro inserido no banco
     *
     * @return int
     * - Caso tenha sido executado apenas um insert, vai retornar o id inserido, ou zero caso não tenha sido inserido.
     * Para todos os outros casos retornará -1;
     */
    public function lastInsertId()
    {
        return $this->insertId;
    }

    /**
     * Define a informação do erro
     *
     * @param string $_info
     */
    public function setErrorInfo($_info)
    {
        $this->errorInfo = $_info;
    }

    /**
     * Retorna a informação do ultimo erro ocorrido
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * Define uma propriedade
     *
     * @param mixed $_attr
     * @param mixed $_val
     */
    public function setAttribute($_attr,$_val)
    {
        $this->attributes[$_attr] = $_val;
    }

    /**
     * Retorna uma propriedade
     *
     * @param mixed $_attr
     * @return mixed
     */
    public function getAttribute($_attr)
    {
        return array_key_exists($_attr, $this->attributes) ? $this->attributes[$_attr] : null;
//		return in_array($_attr, $this->attributes) ? $this->attributes[$_attr] : null;
    }

    /**
     * Retorna o status da execução do script
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    public static function utf8EncodeArrayItem(&$_val)
    {
        $_val = utf8_encode($_val);
    }

    public function __toString()
    {
        return __CLASS__;
    }

    private function convertDate($toDateTime, $dbTimezone, $reg)
    {
        foreach ($toDateTime as $col) {
            $col = !is_array($col) ? [$col, 'Y-m-d H:i:s'] : $col;
            $reg[$col[0]] = !is_null($reg[$col[0]]) && !empty($reg[$col[0]])
                ? DateTime::createFromFormat($col[1], DateTime::fitToFormat($reg[$col[0]], $col[1]), $dbTimezone)
                : null;
        }

        return $reg;
    }
}