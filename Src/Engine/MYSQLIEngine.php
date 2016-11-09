<?php
namespace DatabaseLayer\Src\Engine;

class MYSQLIEngine implements EngineInterface
{
    public function connect($_serverName, array $_options)
    {
        $_userName	= !empty($_options['UID'])				? $_options['UID']				: null;
        $_password	= !empty($_options['PWD'])				? $_options['PWD']				: null;
        $_database	= !empty($_options['DB'])				? $_options['DB']				: null;

        return mysqli_connect($_serverName, $_userName, $_password, $_database);
    }

    public function selectDB($_databaseName, $_linkIdentifier = null)
    {
        return mysqli_select_db($_linkIdentifier, $_databaseName);
    }

    public function close($_linkIdentifier = null)
    {
        return mysqli_close($_linkIdentifier);
    }

    public function query($_linkIdentifier, $_query, $_params = [], $_options = [])
    {
        return mysqli_query($_linkIdentifier, $_query);
    }

    public function errno($_linkIdentifier = null)
    {
        return mysqli_errno($_linkIdentifier);
    }

    public function error($_linkIdentifier = null)
    {
        return mysqli_error($_linkIdentifier);
    }

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null)
    {
        return mysqli_fetch_array($_result,$_resultType);
    }

    public function fetchObject($_result,$_className=null,$_ctorArgs=null,$_row=null,$_offset=null)
    {
        return mysqli_fetch_object($_result);
    }

    public function numRows($_result)
    {
        return mysqli_num_rows($_result);
    }

    public function numFields($_result)
    {
        return mysqli_num_fields($_result);
    }

    public function rowsAffected($_linkIdentifier)
    {
        return mysqli_affected_rows($_linkIdentifier);
    }

    /**
     * @param $_result
     * @param $_index
     * @return array
     */
    public function fetchField($_result, $_index = -1) {
        $col = mysqli_fetch_field($_result);

        if(!is_object($col)) {
            $status = false;

            $col = new \stdClass();
            $col->name = null;
            $col->max_length = null;
            $col->column_source = null;
            $col->numeric = null;
            $col->type = null;
        } else
            $status = true;

        return ['status' => $status, 'name' => $col->name, 'max_length' => $col->max_length, 'column_source' => $col->column_source, 'numeric' => $col->numeric, 'type' => $col->type];
    }

    public function insertId($_linkIdentifier = null)
    {
        return mysqli_insert_id($_linkIdentifier);
    }

    public function freeResult($_result) {
        mysqli_free_result($_result);
    }

    /**
     * @internal
     */
    public function __toString()
    {
        return __CLASS__;
    }
}