<?php
namespace DatabaseLayer\Src\Engine;

use DatabaseLayer\Src\QueryHelpers\QueryOptions;
use DatabaseLayer\Src\QueryHelpers\TransactionOptions;

interface EngineInterface
{
    public function connect($_configuration);

    public function selectDB($_databaseName);

    public function close();

    public function query($_query, $_params = [], $_options = []);

    public function errno();

    public function error();

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null);

    public function fetchObject($_result,$_className=null,$_constructorArgs=null,$_row=null,$_offset=null);

    public function numRows($_result);

    public function numFields($_result);

    public function rowsAffected();

    public function fetchField($_result, $_index = -1);

    public function insertId();

    public function freeResult($_result);

    /**
     * Check if the needed extension is loaded
     * @return boolean
     */
    public function isExtensionLoaded();

    /**
     * Extension name
     * @return string
     */
    public function getExtension();

    /**
     * Engine is connected and everything is fine
     * @return boolean
     */
    public function isOK();

    /**
     * @return boolean
     */
    public function isConvertionToUtf8Needed();

    public function runQuery($_query, QueryOptions $_options = null);

    public function transaction(array $_queries, TransactionOptions $_options);

    /**
     * @return array
     */
    public function getOpenTransactions();

    /**
     * @param $_transactionName
     * @return
     */
    public function getOpenTransactionByName($_transactionName);

    public function resetTransactions();

    public function openTransaction($_tName = null);

    public function rollbackTransaction($_tName = null);

    public function commitTransaction($_tName = null);

    /**
     * @return string
     */
    public function getTimezone();
}