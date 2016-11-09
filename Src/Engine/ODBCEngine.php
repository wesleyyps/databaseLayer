<?php
namespace DatabaseLayer\Src\Engine;

class ODBCEngine implements EngineInterface
{
    private $res;

    public function connect($_serverName, array $_options)
    {
        $_userName      = !empty($_options['UID'])				? $_options['UID']				: null;
        $_password      = !empty($_options['PWD'])				? $_options['PWD']				: null;
        /**
         * O DSN permite conectar em qualquer tipo de fonte de dados odbc. O valor padrão é para conectar com o Sql Server,
         * mas é possível conectar em fontes do excel e do access por exemplo.
         */
        $dsn            = !empty($_options['ODBC_DSN'])         ? $_options['ODBC_DSN']         :
                                                                "Driver={SQL Server Native Client 10.0};Server=%s;";
        return odbc_connect(sprintf($dsn, '{'.$_serverName.'}'), $_userName, $_password);
    }

    public function close($_linkIdentifier = null)
    {
        odbc_close($_linkIdentifier);
        return true;
    }

    public function query($_linkIdentifier, $_query, $_params = [], $_options = [])
    {
        $this->res = odbc_exec($_linkIdentifier,$_query);
        return $this->res;
    }

    public function errno($_linkIdentifier = null)
    {
        return odbc_error($_linkIdentifier);
    }

    public function error($_linkIdentifier = null)
    {
        return odbc_errormsg($_linkIdentifier);
    }

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null)
    {
        return odbc_fetch_array($_result, $_row);
    }

    public function fetchObject($_result,$_className=null,$_ctorArgs=null,$_row=null,$_offset=null)
    {
        return odbc_fetch_object($_result,$_row);
    }

    public function numRows($_result)
    {
        return odbc_num_rows($_result);
    }

    public function numFields($_result)
    {
        return odbc_num_fields($_result);
    }

    public function rowsAffected($_linkIdentifier)
    {
        return odbc_num_rows($this->res);
    }

    public function fetchField($_result, $_index = -1) {
        $_index++;

        $status = true;

        $numeric = null;

        $name = odbc_field_name($_result, $_index);
        if($name === false) {
            $status = false;
            $name = null;
        } else {
            $column_source = $name;
        }

        $max_length = odbc_field_len($_result, $_index);
        if($max_length === false) {
            $status = false;
            $max_length = null;
        }

        $type = odbc_field_type($_result, $_index);
        if($type === false) {
            $status = false;
            $type = null;
        } else {
            $numeric = in_array($type, ['bigint', 'bit', 'decimal', 'int', 'money', 'numeric',
                                             'smallint', 'smallmoney', 'tinyint','float']);
        }

        return ['status' => $status, 'name' => $name, 'max_length' => $max_length, 'column_source' => $column_source, 'numeric' => $numeric, 'type' => $type];
    }

    public function insertId($_linkIdentifier = null)
    {
        $id = null;

        $res = odbc_exec($_linkIdentifier,'SELECT SCOPE_IDENTITY() as ID;');
        if(is_resource($res) && odbc_num_rows($res))
        {
            $reg = odbc_fetch_array($res);
            $id = intval($reg['ID']);
        }

        return $id;
    }

    public function freeResult($_result) {
        return odbc_free_result($_result);
    }

    /**
     * @internal
     */
    public function __toString()
    {
        return __CLASS__;
    }
}