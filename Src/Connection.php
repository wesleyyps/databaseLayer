<?php
namespace DatabaseLayer\Src;

use DatabaseLayer\Src\Engine\EngineFactory;
use DatabaseLayer\Src\Engine\EngineInterface;
use DatabaseLayer\Src\Exception\InvalidConnectionException;
use DatabaseLayer\Src\QueryHelpers\QueryOptions;
use DatabaseLayer\Src\Resultset\QueryResultset;
use DatabaseLayer\Src\Resultset\TransactionResultset;

class Connection
{
    /*
     * FETCH STYLES
     */
    const FETCH_ASSOC = 1;
    const FETCH_NUM = 2;
    const FETCH_BOTH = 3;
    const FETCH_OBJ = 4;
    const FETCH_CLASS = 5;
    const FETCH_COLUMN = 6;
    const FETCH_TOCLASS = 7;

    const PARAM_HOST = 'host';
    const PARAM_USER = 'user';
    const PARAM_PWD  = 'password';
    const PARAM_OPTIONS  = 'options';
    const PARAM_UTF8ENC = 'utf8';
    const PARAM_ENGINE = 'engine';
    const PARAM_DBTIMEZONE = 'dbtmz';

    /**
     * Não retorna erros
     * @var int
     */
    const ERRMODE_SILENT = 0;
    /**
     * Retorna um E_WARNING quando houver erros
     * @var int
     */
    const ERRMODE_WARNING = 1;
    /**
     * Retorna um E_ERROR (Erro fatal) quando houver erros
     * @var int
     */
    const ERRMODE_EXCEPTION = 2;

    /**
     * @var bool|string
     * Quando o parametro for verdadeiro, qualquer query falha. Quando for string, qualquer instrução começando com a string falha
     */
    static $forceErrorStatus = false;

    /**
     * Instância da conexão
     * @var EngineInterface[]
     */
    static public $instance = [];

    static public $configurationFile = '';
    static public $errorMode = Connection::ERRMODE_EXCEPTION;

    static public function open($_conName = 'default')
    {
        if(!isset(self::$instance[$_conName])) {
            self::$instance[$_conName] = EngineFactory::load($_conName);
        }
        return self::$instance[$_conName];
    }

    static public function selectDB($_dataBase, $_conName='default') {
        $ret = false;
        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $ret = self::$instance[$_conName]->selectDB($_dataBase);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $ret;
    }

    /**
     * @param \Exception $_e
     * @throws \Exception
     */
    static public function errorHandler(\Exception $_e)
    {
        if(Connection::$errorMode == Connection::ERRMODE_EXCEPTION) {
            throw $_e;
        } elseif(Connection::$errorMode == Connection::ERRMODE_WARNING) {
            trigger_error($_e->getMessage(), E_USER_WARNING);
        }
    }

    /**
     * Closes the connection and removes the instance
     * @param string $_conName
     * @static
     */
    static public function close($_conName='default')
    {
        if(isset(self::$instance[$_conName])) {
            self::$instance[$_conName]->close();
            unset(self::$instance[$_conName]);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

    }

    /**
     * Atalho para a função instanciada
     * @param array $_queries
     * @param array $_options
     * @param string $_conName
     * @return TransactionResultset
     * @static
     */
    static public function transaction(array $_queries,$_options= [], $_conName = 'default')
    {
        $ret = null;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $ret = self::$instance[$_conName]->transaction($_queries, $_options, $_conName);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $ret;
    }

    /**
     * Atalho para a função instanciada
     * @param string $_query
     * @param QueryOptions $_options
     * @param string $_conName
     * @return QueryResultset
     * @see Connection->query
     * @static
     */
    static public function query($_query, QueryOptions $_options = null, $_conName = 'default')
    {
        $ret = null;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $ret = self::$instance[$_conName]->runQuery($_query, $_options);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $ret;
    }

    /*
     * Executa uma query no banco de dados e retorna a quantidade de registros afetados
     * @param string $_query - A query para executar
     * @param array $_options - Array de opções para a execução da função. Qualquer chave informada serve para substituir em tempo de execução as opções padrÃ£o de conexão.
     * @return int
     * @throws \ErrorException
     *
    public function execute($_query,$_options= [])
    {
        //$rowsAff = 0;

        $attr = $this->attributes;

        if(count($_options) > 0)
        {
            foreach($_options as $key => $val)
            {
                $attr[$key] = $val;
            }
        }

        $query = trim($_query," ;\n\t\r");

        if(empty($query))
        {
            $erro = 'Um script vazio não pode ser executado!';
            $rowsAff = -1;
        }
        else
        {
            if($this->autoUtf8Decode)
            {
                $query = utf8_decode($query);
            }

            $res = $this->engine->query($this->con,$query);

            if($res == true)
            {

                $rowsAff = intval($this->engine->rowsAffected(($this->driver == 'sqlsrv') ? $res : $this->con));
                $erro = null;
            }
            else
            {
                $rowsAff = -1;

                $erro = $this->engine->error($this->con);
            }
        }

        if(!is_null($erro))
            self::displayError($erro,__LINE__,$attr);

        return $rowsAff;
    }*/

    /**
     * Retorna o tempo atual em segundos
     * @return float
     */
    public static function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Retorna se uma transação esta aberta
     * @param mixed $_tName
     * @param string $_conName
     * @return bool
     */
    public static function getTransactionOpen($_tName = null, $_conName = 'default') {
        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            if (!is_null($_tName)) {
                $res = self::$instance[$_conName]->getOpenTransactionByName($_tName);
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $key = array_search(true, self::$instance[$_conName]->getOpenTransactions());
                $res = $key !== false ? true : false;
            }
        } else {
            $res = false;
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }
        return $res;
    }

    public static function resetTransactionCount($_conName = 'default') {
        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            self::$instance[$_conName]->resetTransactions();
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

    }

    public static function getTransactionList($_conName = 'default') {
        $ret = null;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $ret = self::$instance[$_conName]->getOpenTransactions();
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $ret;
    }

    /**
     * Abre uma transação no banco de dados
     * @param mixed $_tName
     * @param string $_conName
     * @return bool
     * @throws \Exception
     */
    public static function openTransaction($_tName = null, $_conName = 'default') {
        $return = false;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $return = self::$instance[$_conName]->getOpenTransactionByName($_tName);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }


        return $return;
    }

    /**
     * Faz o rollback da transação
     * @param mixed $_tName
     * @param string $_conName
     * @return bool
     * @throws \Exception
     */
    public static function rollbackTransaction($_tName = null, $_conName = 'default') {
        $flag = false;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $flag = self::$instance[$_conName]->rollbackTransaction($_tName);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $flag;
    }

    /**
     * Faz o commit da transação
     * @param mixed $_tName
     * @param string $_conName
     * @return bool
     * @throws \Exception
     */
    public static function commitTransaction($_tName = null, $_conName = 'default') {
        $flag = false;

        if(isset(self::$instance[$_conName]) && self::$instance[$_conName]->isOK()) {
            $flag = self::$instance[$_conName]->commitTransaction($_tName);
        } else {
            Connection::errorHandler(new InvalidConnectionException($_conName, __FILE__, __LINE__));
        }

        return $flag;
    }

    /*
     * @param $_query
     * @param array $_options
     * @return NamedParamStatement
     *
    static public function sPrepareStatement($_query,$_options= [])
    {
        return self::$instance->prepareStatement($_query, $_options);
    }*/

    /*
     * @param $_query
     * @param array $_options
     * @return NamedParamStatement
     *
    public function prepareStatement($_query,$_options= [])
    {
        $attr = array_merge($this->attributes, $_options);

        return new NamedParamStatement($_query, $attr);
    }*/

    public function __toString()
    {
        return __CLASS__;
    }
}