<?php
/**
 * Created by PhpStorm.
 * User: wesley.sousa
 * Date: 21/10/2016
 * Time: 14:18
 */

namespace DatabaseLayer\Src\Engine;


use DatabaseLayer\Src\QueryHelpers\QueryOptions;
use DatabaseLayer\Src\QueryHelpers\TransactionOptions;

class InvalidEngine implements EngineInterface
{
    public function connect($_configuration) {
        return false;
    }

    public function selectDB($_databaseName) {
        return false;
    }

    public function close() {
        return false;
    }

    public function query($_query, $_params = [], $_options = []) {
        return false;
    }

    public function errno() {
        return false;
    }

    public function error() {
        return false;
    }

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null) {
        return false;
    }

    public function fetchObject($_result,$_className=null,$_constructorArgs=null,$_row=null,$_offset=null) {
        return false;
    }

    public function numRows($_result) {
        return false;
    }

    public function numFields($_result) {
        return false;
    }

    public function rowsAffected() {
        return false;
    }

    public function fetchField($_result, $_index = -1) {
        return false;
    }

    public function insertId() {
        return false;
    }

    public function freeResult($_result) {
        return false;
    }

    public function isExtensionLoaded() {
        return false;
    }

    public function getExtension() {
        return '';
    }

    public function isOK() {
        return false;
    }

    /**
     * @return boolean
     */
    public function isConvertionToUtf8Needed(){
        return false;
    }

    public function runQuery($_query, QueryOptions $_options = null) {
        return false;
    }

    public function transaction(array $_queries, TransactionOptions $_options) {
        return false;
    }

    /**
     * @return array
     */
    public function getOpenTransactions() {
        return false;
    }

    public function getOpenTransactionByName($_transactionName) {
        return false;
    }

    public function resetTransactions() {
        return false;
    }

    public function openTransaction($_tName = null) {
        return false;
    }

    public function rollbackTransaction($_tName = null) {
        return false;
    }

    public function commitTransaction($_tName = null) {
        return false;
    }

    public function getTimezone() {
        return '';
    }
}