<?php

//Start of SqlSrv v.1.0-dev

/**
 * @link http://www.php.net/manual/en/book.sqlsrv.php
 */

/**
 * Begins a database transaction
 * @param connection resource
 * <p>The connection resource returned by a call to sqlsrv_connect().</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p.
 * @link http://www.php.net/manual/en/function.sqlsrv-begin-transaction.php
 */
function sqlsrv_begin_transaction ($conn) {}

/**
 * Cancels a statement
 * @param statement resource
 * <p>The statement resource to be cancelled</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-cancel.php
 */
function sqlsrv_cancel ($stmt) {}

/**
 * Returns information about the client and specified connection 
 * @param resource $conn
 * <p>The connection about which information is returned.</p>
 * @return mixed <p>Returns an associative array with keys described in the table below.
 * Returns FALSE otherwise.Array returned by sqlsrv_client_info 
 * Key - Description
 * DriverDllName - SQLNCLI10.DLL
 * DriverODBCVer - ODBC version (xx.yy)
 * DriverVer - SQL Server Native Client DLL version (10.5.xxx)
 * ExtensionVer - php_sqlsrv.dll version (2.0.xxx.x)</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-client-info.php
 */
function sqlsrv_client_info ($conn) {}

/**
 * Closes an open connection and releases resourses associated with the connection
 * @param resource $conn
 * <p>The connection to be closed.</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-close.php
 */
function sqlsrv_close ($conn) {}

/**
 * Commits a transaction that was begun with sqlsrv_begin_transaction()
 * @param resource $conn
 * <p>The connection on which the transaction is to be committed.</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-commit.php
 */
function sqlsrv_commit ($conn) {}

/**
 * Changes the driver error handling and logging configurations
 * @param string $setting
 * <p>The name of the setting to set. The possible values are 
 * "WarningsReturnAsErrors", "LogSubsystems", and "LogSeverity".</p>
 * @param mixed $value
 * <p>The value of the specified setting. The following table shows possible values:
 * WarningsReturnAsErrors: 1 (TRUE) or 0 (FALSE)
 * LogSubsystems: SQLSRV_LOG_SYSTEM_ALL (-1), SQLSRV_LOG_SYSTEM_CONN (2), 
 * SQLSRV_LOG_SYSTEM_INIT (1), SQLSRV_LOG_SYSTEM_OFF (0), SQLSRV_LOG_SYSTEM_STMT (4),
 * SQLSRV_LOG_SYSTEM_UTIL (8)
 * LogSeverity: SQLSRV_LOG_SEVERITY_ALL (-1), SQLSRV_LOG_SEVERITY_ERROR (1), 
 * SQLSRV_LOG_SEVERITY_NOTICE (4), SQLSRV_LOG_SEVERITY_WARNING (2)</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-configure.php
 */
function sqlsrv_configure ($setting, $value) {}

/**
 * Opens a connection to a Microsoft SQL Server database
 * @param string $serverName
 * <p>The name of the server to which a connection is established. To connect to 
 * a specific instance, follow the server name with a forward slash and the instance 
 * name (e.g. serverName\sqlexpress).</p>
 * @param array[optional] $connectionInfo
 * <p>An associative array that specifies options for connecting to the server. If 
 * values for the UID and PWD keys are not specified, the connection will be attempted 
 * using Windows Authentication. For a complete list of supported keys, see SQLSRV 
 * Connection Options.</p>
 * @return mixed <p>A connection resource. If a connection cannot be successfully opened, '
 * FALSE is returned.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-connect.php
 */
function sqlsrv_connect ($serverName, $connectionInfo = []) {}

/**
 * Returns error and warning information about the last SQLSRV operation performed
 * @param int[optional] $errorsOrWarnings
 * <p>Determines whether error information, warning information, or both are returned. 
 * If this parameter is not supplied, both error information and warning information 
 * are returned. The following are the supported values for this parameter: 
 * SQLSRV_ERR_ALL, SQLSRV_ERR_ERRORS, SQLSRV_ERR_WARNINGS.</p>
 * @return mixed <p>If errors and/or warnings occured on the last sqlsrv operation, and array
 * of arrays containing error information is returned. If no errors and/or warnings occured 
 * on the last sqlsrv operation, NULL is returned. The following table describes the structure 
 * of the returned arrays: Array returned by sqlsrv_errors 
 * Key - Description
 * SQLSTATE - For errors that originate from the ODBC driver, the SQLSTATE returned by ODBC. 
 * For errors that originate from the Microsoft Drivers for PHP for SQL Server, a SQLSTATE of 
 * IMSSP. For warnings that originate from the Microsoft Drivers for PHP for SQL Server, a 
 * SQLSTATE of 01SSP.
 * code - For errors that originate from SQL Server, the native SQL Server error code. For 
 * errors that originate from the ODBC driver, the error code returned by ODBC. For errors 
 * that originate from the Microsoft Drivers for PHP for SQL Server, the Microsoft Drivers 
 * for PHP for SQL Server error code.
 * message - A description of the error.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-errors.php
 */
function sqlsrv_errors ($errorsOrWarnings = null) {}

/**
 * Executes a statement prepared with sqlsrv_prepare()
 * @param resource $stmt
 * <p>A statement resource returned by sqlsrv_prepare().</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-execute.php
 */
function sqlsrv_execute ($stmt) {}

/**
 * Returns a row as an array
 * @param resource $stmt
 * <p>A statement resource returned by sqlsrv_query or sqlsrv_execute.</p>
 * @param int[optional] $fetchType
 * <p>A predefined constant specifying the type of array to return. Possible values 
 * are SQLSRV_FETCH_ASSOC, SQLSRV_FETCH_NUMERIC, and SQLSRV_FETCH_BOTH (the default).
 * A fetch type of SQLSRV_FETCH_ASSOC should not be used when consuming a result set 
 * with multiple columns of the same name.</p>
 * @param int[optional] $row
 * <p>Specifies the row to access in a result set that uses a scrollable cursor. 
 * Possible values are SQLSRV_SCROLL_NEXT, SQLSRV_SCROLL_PRIOR, SQLSRV_SCROLL_FIRST, 
 * SQLSRV_SCROLL_LAST, SQLSRV_SCROLL_ABSOLUTE and, SQLSRV_SCROLL_RELATIVE (the default). 
 * When this parameter is specified, the fetchType must be explicitly defined.</p>
 * @param int[optional] $offset
 * <p>Used with SQLSRV_SCROLL_ABSOLUTE and SQLSRV_SCROLL_RELATIVE to specify the row 
 * to retrieve. The first record in the result set is 0.</p>
 * @return mixed <p>Returns an array on success, NULL if there are no more rows to return,
 * and FALSE if an error occurs.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-fetch-array.php
 */
function sqlsrv_fetch_array ($stmt, $fetchType = null, $row = null, $offset = null) {}

/**
 * Retrieves the next row of data in a result set as an object
 * @param resource $stmt
 * <p>A statement resource created by sqlsrv_query() or sqlsrv_execute().</p>
 * @param string[optional] $className
 * <p>The name of the class to instantiate. If no class name is specified, stdClass 
 * is instantiated.</p>
 * @param string[optional] $ctorParams
 * <p>Values passed to the constructor of the specified class. If the constructor 
 * of the specified class takes parameters, the ctorParams array must be supplied.</p>
 * @param int[optional] $row
 * <p>The row to be accessed. This parameter can only be used if the specified statement 
 * was prepared with a scrollable cursor. In that case, this parameter can take on one of 
 * the following values: SQLSRV_SCROLL_NEXT, SQLSRV_SCROLL_PRIOR, SQLSRV_SCROLL_FIRST, 
 * SQLSRV_SCROLL_LAST, SQLSRV_SCROLL_ABSOLUTE, SQLSRV_SCROLL_RELATIVE</p>
 * @param int[optional] $offset
 * <p>Specifies the row to be accessed if the row parameter is set to SQLSRV_SCROLL_ABSOLUTE 
 * or SQLSRV_SCROLL_RELATIVE. Note that the first row in a result set has index 0.</p>
 * @return  mixed <p>Returns an object on success, NULL if there are no more rows to return, and
 * FALSE if an error occurs or if the specified class does not exist.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-fetch-object.php
 */
function sqlsrv_fetch_object ( $stmt, $className = null, $ctorParams = null, $row = null, $offset = null) {}

/**
 * Makes the next row in a result set available for reading
 * @param resource $stmt
 * <p>A statement resource created by executing sqlsrv_query() or sqlsrv_execute().</p>
 * @param int[optional] $row
 * <p>The row to be accessed. This parameter can only be used if the specified statement was 
 * prepared with a scrollable cursor. In that case, this parameter can take on one of the 
 * following values: SQLSRV_SCROLL_NEXT, SQLSRV_SCROLL_PRIOR, SQLSRV_SCROLL_FIRST, 
 * SQLSRV_SCROLL_LAST, SQLSRV_SCROLL_ABSOLUTE, SQLSRV_SCROLL_RELATIVE</p>
 * @param int[optional] $offset
 * <p>Specifies the row to be accessed if the row parameter is set to SQLSRV_SCROLL_ABSOLUTE 
 * or SQLSRV_SCROLL_RELATIVE. Note that the first row in a result set has index 0.</p>
 * @return mixed <p>Returns TRUE if the next row of a result set was successfully retrieved,
 * FALSE if an error occurs, and NULL if there are no more rows in the result set.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-fetch.php 
 */
function sqlsrv_fetch ($stmt, $row = null, $offset = null) {}

/**
 * Retrieves metadata for the fields of a statement prepared by sqlsrv_prepare() 
 * or sqlsrv_query()
 * @param resource $stmt
 * <p>The statment resource for which metadata is returned.</p>
 * @return mixed <p>Returns an array of arrays is returned on success. Otherwise, FALSE is
 * returned. Each returned array is described by the following table:
 * Array returned by sqlsrv_field_metadata 
 * Key - Description
 * Name - The name of the field.
 * Type - The numeric value for the SQL type.
 * Size - The number of characters for fields of character type, the number of bytes 
 * for fields of binary type, or NULL for other types.
 * Precision - The precision for types of variable precision, NULL for other types.
 * Scale - The scale for types of variable scale, NULL for other types.
 * Nullable - An enumeration indicating whether the column is nullable, not nullable, or 
 * if it is not known.
 * For more information, see � sqlsrv_field_metadata in the Microsoft SQLSRV documentation. </p>
 * @link http://www.php.net/manual/en/function.sqlsrv-field-metadata.php
 */
function sqlsrv_field_metadata ($stmt) {}

/**
 * Frees all resources for the specified statement
 * @param resource $stmt
 * <p>The statment for which resources are freed. Note that NULL is a valid parameter value. 
 * This allows the function to be called multiple times in a script.</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-free-stmt.php
 */
function sqlsrv_free_stmt ($stmt) {}

/**
 * Returns the value of the specified configuration setting
 * @param string $setting
 * <p>The name of the setting for which the value is returned. For a list of 
 * configurable settings, see sqlsrv_configure().</p>
 * @return bool <p>Returns the value of the specified setting. If an invalid setting is specified,
 * FALSE is returned.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-get-config.php
 */
function sqlsrv_get_config ($setting) {}

/**
 * Gets field data from the currently selected row
 * @param resource $stmt
 * <p>A statement resource returned by sqlsrv_query() or sqlsrv_execute().</p>
 * @param int $fieldIndex
 * <p>The index of the field to be retrieved. Field indices start at 0. Fields 
 * must be accessed in order. i.e. If you access field index 1, then field index 0 
 * will not be available.</p>
 * @param int[optional] $getAsType
 * <p>The PHP data type for the returned field data. If this parameter is not set, 
 * the field data will be returned as its default PHP data type. For information about 
 * default PHP data types, see � Default PHP Data Types in the Microsoft SQLSRV 
 * documentation.</p>
 * @return mixed <p>Returns data from the specified field on success. Returns FALSE otherwise.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-get-field.php
 */
function sqlsrv_get_field ($stmt, $fieldIndex, $getAsType = null) {}

/**
 * Indicates whether the specified statement has rows
 * @param resource $stmt
 * <p>A statement resource returned by sqlsrv_query() or sqlsrv_execute().</p>
 * @return bool <p>Returns TRUE if the specified statement has rows and FALSE if the statement
 * does not have rows or if an error occured. </p>
 * @link http://www.php.net/manual/en/function.sqlsrv-has-rows.php
 */
function sqlsrv_has_rows ($stmt) {}

/**
 * Makes the next result of the specified statement active
 * @param resource $stmt
 * <p>The statment on which the next result is being called.</p>
 * @return bool <p>Returns TRUE if the next result was successfully retrieved, FALSE if an
 * error occurred, and NULL if there are no more results to retrieve.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-next-result.php
 */
function sqlsrv_next_result ($stmt) {}

/**
 * Retrieves the number of fields (columns) on a statement
 * @param resource $stmt
 * <p>The statment for which the number of fields is returned. sqlsrv_num_fields() 
 * can be called on a statement before or after statement execution.</p>
 * @return bool <p>Returns the number of fields on success. Returns FALSE otherwise.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-num-fields.php
 */
function sqlsrv_num_fields ($stmt) {}

/**
 * Retrieves the number of rows in a result set
 * @param resource $stmt
 * <p>The statement for which the row count is returned. The statment resource must 
 * be created with a static or keyset cursor. For more information, see sqlsrv_query(), 
 * sqlsrv_prepare(), or 
 * Specifying a Cursor Type and Selecting Rows in the Microsoft SQLSRV documentation.</p>
 * @return mixed <p>Returns the number of rows retrieved on success and FALSE if an error
 * occured. If a forward cursor (the default) or dynamic cursor is used, FALSE is returned.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-num-rows.php
 */
function sqlsrv_num_rows ($stmt) {}

/**
 * Prepares a query for execution
 * @param resource $conn
 * <p>A connection resource returned by sqlsrv_connect().</p>
 * @param string $sql
 * <p>The string that defines the query to be prepared and executed.</p>
 * @param array[optional] $params
 * <p>An array specifying parameter information when executing a parameterized query. 
 * Array elements can be any of the following: A literal value, A PHP variable, An array 
 * with this structure: array($value [, $direction [, $phpType [, $sqlType]]]) 
 * 
 * The following table describes the elements in the array structure above:
 * Array structure Element 	Description
 * $value 	A literal value, a PHP variable, or a PHP by-reference variable 
 * $direction (optional) 	One of the following SQLSRV constants used to indicate the 
 * parameter direction: SQLSRV_PARAM_IN, SQLSRV_PARAM_OUT, SQLSRV_PARAM_INOUT. The default 
 * value is SQLSRV_PARAM_IN.
 * $phpType (optional) 	A SQLSRV_PHPTYPE_* constant that specifies PHP data type of the 
 * returned value. 
 * $sqlType (optional) 	A SQLSRV_SQLTYPE_* constant that specifies the SQL Server data 
 * type of the input value.
 * </p>
 * @param array[optional] $options
 * An array specifing query property options. The supported keys are described in the 
 * following table:
 * Key - Values - Description
 * QueryTimeout - A positive integer value. - Sets the query timeout in seconds. By default, 
 * the driver will wait indefinitely for results.
 * SendStreamParamsAtExec - TRUE or FALSE (the default is TRUE) - Configures the driver to 
 * send all stream data at execution (TRUE), or to send stream data in chunks (FALSE). 
 * By default, the value is set to TRUE. For more information, see sqlsrv_send_stream_data().
 * Scrollable - SQLSRV_CURSOR_FORWARD, SQLSRV_CURSOR_STATIC, SQLSRV_CURSOR_DYNAMIC, or 
 * SQLSRV_CURSOR_KEYSET - See Specifying a Cursor Type and Selecting Rows in the 
 * Microsoft SQLSRV documentation.
 * @return bool <p>Returns a statement resource on success and FALSE if an error occurred.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-prepare.php
 */
function sqlsrv_prepare ($conn, $sql, $params = null, $options = null) {}

/**
 * Prepares and executes a query.
 * @param resource $conn
 * <p>A connection resource returned by sqlsrv_connect().</p>
 * @param string $sql
 * <p>The string that defines the query to be prepared and executed.</p>
 * @param array[optional] $params
 * <p>An array specifying parameter information when executing a parameterized query. 
 * Array elements can be any of the following: A literal value, A PHP variable, An array 
 * with this structure: array($value [, $direction [, $phpType [, $sqlType]]]) 
 * 
 * The following table describes the elements in the array structure above:
 * Array structure Element 	Description
 * $value 	A literal value, a PHP variable, or a PHP by-reference variable 
 * $direction (optional) 	One of the following SQLSRV constants used to indicate the 
 * parameter direction: SQLSRV_PARAM_IN, SQLSRV_PARAM_OUT, SQLSRV_PARAM_INOUT. The default 
 * value is SQLSRV_PARAM_IN.
 * $phpType (optional) 	A SQLSRV_PHPTYPE_* constant that specifies PHP data type of the 
 * returned value. 
 * $sqlType (optional) 	A SQLSRV_SQLTYPE_* constant that specifies the SQL Server data 
 * type of the input value.
 * </p>
 * @param array[optional] $options
 * An array specifing query property options. The supported keys are described in the 
 * following table:
 * Key - Values - Description
 * QueryTimeout - A positive integer value. - Sets the query timeout in seconds. By default, 
 * the driver will wait indefinitely for results.
 * SendStreamParamsAtExec - TRUE or FALSE (the default is TRUE) - Configures the driver to 
 * send all stream data at execution (TRUE), or to send stream data in chunks (FALSE). 
 * By default, the value is set to TRUE. For more information, see sqlsrv_send_stream_data().
 * Scrollable - SQLSRV_CURSOR_FORWARD, SQLSRV_CURSOR_STATIC, SQLSRV_CURSOR_DYNAMIC, or 
 * SQLSRV_CURSOR_KEYSET - See Specifying a Cursor Type and Selecting Rows in the 
 * Microsoft SQLSRV documentation.
 * @return bool <p>Returns a statement resource on success and FALSE if an error occurred.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-query.php
 */
function sqlsrv_query ($conn, $sql, $params = null, $options = null) {}

/**
 * Rolls back a transaction that was begun with sqlsrv_begin_transaction()
 * @param resource $conn
 * <p>The connection resource returned by a call to sqlsrv_connect().</p>
 * @return bool <p>Returns TRUE on success or FALSE on failure.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-rollback.php
 */
function sqlsrv_rollback ($conn) {}

/**
 * Returns the number of rows modified by the last INSERT, UPDATE, or DELETE query executed
 * @param resource $stmt
 * <p>The executed statement resource for which the number of affected rows is returned.</p>
 * @return mixed <p>Returns the number of rows affected by the last INSERT, UPDATE, or DELETE query.
 * If no rows were affected, 0 is returned. If the number of affected rows cannot be 
 * determined, -1 is returned. If an error occured, FALSE is returned.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-rows-affected.php
 */
function sqlsrv_rows_affected ($stmt) {}

/**
 * Sends data from parameter streams to the server
 * @param resource $stmt
 * <p>A statement resource returned by sqlsrv_query() or sqlsrv_execute().</p>
 * @return bool <p>Returns TRUE if there is more data to send and FALSE if there is not.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-send-stream-data.php
 */
function sqlsrv_send_stream_data ($stmt) {}

/**
 * Returns information about the server
 * @param resource $conn
 * <p>The connection resource that connects the client and the server.</p>
 * @return mixed <p>Returns an array as described in the following table:
 * Returned Array 
 * CurrentDatabase - The connected-to database.
 * SQLServerVersion - The SQL Server version.
 * SQLServerName - The name of the server.</p>
 * @link http://www.php.net/manual/en/function.sqlsrv-server-info.php
 */
function sqlsrv_server_info ($conn) {}