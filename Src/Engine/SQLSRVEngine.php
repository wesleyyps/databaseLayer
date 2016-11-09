<?php
namespace DatabaseLayer\Src\Engine;

use DatabaseLayer\Src\Connection;

class SQLSRVEngine implements EngineInterface
{
    public function connect($_serverName, array $_options)
    {
        return sqlsrv_connect($_serverName,$_options);
    }

    public function close($_linkIdentifier = null)
    {
        return sqlsrv_close($_linkIdentifier);
    }

    public function query($_linkIdentifier, $_query, $_params = [], $_options = [])
    {
        return sqlsrv_query($_linkIdentifier,$_query,$_params,$_options);
    }

    public function errno($_linkIdentifier = null)
    {
        return 0;
    }

    public function error($_linkIdentifier = null)
    {
        $errors = sqlsrv_errors();

        $msgs = [];
        if(is_array($errors)) {
            foreach ($errors as $err) {
                $msgs[] = "{$err['code']} - {$err['message']}";
            }
        }

        return implode("\n\n",$msgs);
    }

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null)
    {
        if($_resultType == Connection::FETCH_ASSOC) {
            $_resultType = SQLSRV_FETCH_ASSOC;
        } elseif($_resultType == Connection::FETCH_NUM) {
            $_resultType = SQLSRV_FETCH_NUMERIC;
        }

        if(is_null($_row))
            $_row = SQLSRV_SCROLL_NEXT;

        return sqlsrv_fetch_array($_result,$_resultType,$_row,$_offset);
    }

    public function fetchObject($_result,$_className=null,$_ctorArgs=null,$_row=null,$_offset=null)
    {
        return sqlsrv_fetch_object($_result,$_className,$_ctorArgs,$_row,$_offset);
    }

    public function numRows($_result)
    {
        return sqlsrv_num_rows($_result);
    }

    public function numFields($_result)
    {
        return sqlsrv_num_fields($_result);
    }

    /**
     * (non-PHPdoc)
     * @see core/libs/db/AbstractEngine::rowsAffected()
     * Nota: Esta função precisa receber o resultado da função query, e não a conexão
     * @param $_sqlStmResult
     * @return bool|int|mixed
     */
    public function rowsAffected($_sqlStmResult)
    {
        return sqlsrv_rows_affected($_sqlStmResult);
    }

    public function fetchField($_result, $_index = -1) {
        //return sqlsrv_get_field($_result, $_index);

        $status = false;
        $name = null;
        $max_length = null;
        $column_source = null;
        $numeric = null;
        $type = null;

        return ['status' => $status, 'name' => $name, 'max_length' => $max_length, 'column_source' => $column_source, 'numeric' => $numeric, 'type' => $type];
    }

    public function insertId($_linkIdentifier = null)
    {
        $id = null;

        $res = sqlsrv_query($_linkIdentifier,'SELECT SCOPE_IDENTITY() as ID;');
        if(is_resource($res) && sqlsrv_num_rows($res))
        {
            $reg = sqlsrv_fetch_array($res, 1);
            $id = intval($reg['ID']);
        }

        return $id;
    }

    public function freeResult($_result) {
        return sqlsrv_free_stmt($_result);
    }

    /**
     * @internal
     */
    public function __toString()
    {
        return __CLASS__;
    }
}