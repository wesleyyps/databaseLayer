<?php
namespace DatabaseLayer\Src\Engine;

use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\Exception\QueryException;
use DatabaseLayer\Src\Exception\TransactionException;
use DatabaseLayer\Src\Exception\ValidationException;
use DatabaseLayer\Src\QueryHelpers\QueryOptions;
use DatabaseLayer\Src\QueryHelpers\TransactionOptions;
use DatabaseLayer\Src\Resultset\QueryResultset;
use DatabaseLayer\Src\Resultset\TransactionResultset;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

class MSSQLEngine implements EngineInterface
{
    const PARAM_NEWLINK = 'ConnectionPooling';

    /**
     * @var resource
     */
    private $con;
    /**
     * @var bool
     */
    private $utf8Convert = false;
    /**
     * @var bool
     */
    private $isOK = false;
    /**
     * @var string Valid timezone
     */
    private $timezone = null;

    /**
     * Manualy added transaction index
     * @var array
     */
    private $transactionOpen = [];

    public function getExtension()
    {
        return 'mssql';
    }

    public function isExtensionLoaded()
    {
        return extension_loaded(self::getExtension());
    }

    public function connect($_configuration)
    {
        $stringValidator = Validator::stringType()->notBlank();

        $validation = Validator
            ::key(Connection::PARAM_HOST, $stringValidator, true)
            ->key(Connection::PARAM_USER, $stringValidator, false)
            ->key(Connection::PARAM_PWD, $stringValidator, false)
            ->key(Connection::PARAM_OPTIONS, Validator::arrayType(), false)
            ->key(Connection::PARAM_UTF8ENC, Validator::boolType(), false)
            ->key(Connection::PARAM_DBTIMEZONE, $stringValidator, true)
        ;

        try {
            $ret = $validation->assert($_configuration);
        } catch(ExceptionInterface $_e) {
            $ret = false;
            Connection::errorHandler(new ValidationException($_e->getMessage(), $_e->getCode(), E_ERROR, __FILE__, __LINE__, $_e->getPrevious()));
        }

        if($ret) {
            $this->utf8Convert = isset($_configuration[Connection::PARAM_UTF8ENC])
                ? $_configuration[Connection::PARAM_UTF8ENC]
                : false
            ;

            $_userName = !empty($_configuration[Connection::PARAM_USER])
                ? $_configuration[Connection::PARAM_USER]
                : null
            ;
            $_password = !empty($_configuration[Connection::PARAM_PWD])
                ? $_configuration[Connection::PARAM_PWD]
                : null
            ;
            $_newLink = !empty($_configuration[Connection::PARAM_OPTIONS][self::PARAM_NEWLINK]) ? $_configuration[Connection::PARAM_OPTIONS][self::PARAM_NEWLINK] : null;

            if(isset($_configuration[Connection::PARAM_DBTIMEZONE]) && !is_null($_configuration[Connection::PARAM_DBTIMEZONE])) {
                $this->timezone = $_configuration[Connection::PARAM_DBTIMEZONE];
            }

            $this->con = mssql_connect($_configuration[Connection::PARAM_HOST],$_userName,$_password,$_newLink);
            if(is_resource($this->con)) {
                $this->isOK = true;
            }
        } else {
            $ret = false;
        }

        return $ret;
    }

    public function selectDB($_databaseName)
    {
        $res = mssql_select_db($_databaseName, $this->con);
        if($res) {
            $this->isOK = true;
        }

        return $res;
    }

    public function close()
    {
        return mssql_close($this->con);
    }

    public function query($_query, $_params = [], $_options = [])
    {
        $_batchSize = !empty($_params['batchSize']) ? $_params['batchSize'] : null;
        return mssql_query($_query, $this->con, $_batchSize);
    }

    public function errno()
    {
        return 0;
    }

    public function error()
    {
        return mssql_get_last_message();
    }

    public function fetchArray($_result, $_resultType = null, $_row = null, $_offset = null)
    {
        return mssql_fetch_array($_result,$_resultType);
    }

    public function fetchObject($_result,$_className=null,$_ctorArgs=null,$_row=null,$_offset=null)
    {
        return mssql_fetch_object($_result);
    }

    public function numRows($_result)
    {
        return mssql_num_rows($_result);
    }

    public function numFields($_result)
    {
        return mssql_num_fields($_result);
    }

    public function rowsAffected()
    {
        return mssql_rows_affected($this->con);
    }

    /**
     * @param $_result
     * @param $_index
     * @return array
     */
    public function fetchField($_result, $_index = -1) {
        $col = mssql_fetch_field($_result, $_index);

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

    public function insertId()
    {
        $id = null;

        $res = mssql_query('SELECT SCOPE_IDENTITY() as ID;', $this->con);
        if(is_resource($res) && mssql_num_rows($res))
        {
            $reg = mssql_fetch_array($res,MSSQL_ASSOC);
            $id = intval($reg['ID']);
        }

        return $id;
    }

    public function freeResult($_result) {
        return mssql_free_result($_result);
    }

    public function isOK()
    {
        return $this->isOK;
    }

    /**
     * Remove nonprint chars and single quotes
     * @param mixed $_data
     * @return mixed
     */
    public static function escape($_data)
    {
        if (is_null($_data) || trim($_data)== '') {
            return '';
        }
        if (is_numeric($_data)) {
            return $_data;
        }

        $non_displayables = [
            '/%0[0-8bcef]/',// url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',// url encoded 16-31
            '/[\x00-\x08]/',// 00-08
            '/\x0b/',// 11
            '/\x0c/',// 12
            '/[\x0e-\x1f]/'// 14-31
        ];
        foreach ($non_displayables as $regex)
            $_data = preg_replace($regex, '', $_data);
        $_data = str_replace("'", "''", $_data);

        return $_data;
    }

    /**
     * Escapa caracteres especiais
     * @param string $_string
     * @param string $_encoding
     * @return string
     */
    public static function mbSqlRegcase($_string,$_encoding='auto')
    {
        $max = mb_strlen($_string,$_encoding);
        $ret = '';
        for ($i = 0; $i < $max; $i++)
        {
            $char = mb_substr($_string,$i,1,$_encoding);
            $up = mb_strtoupper($char,$_encoding);
            $low = mb_strtolower($char,$_encoding);
            $ret.= ($up!=$low) ? '['.$up.$low.']' : $char;
        }
        return $ret;
    }

    /**
     * Executa uma query no banco de dados
     * @param string $_query - A query para executar
     * @param QueryOptions $_options
     * - Array de opções para a execução da função. As seguintes chaves são de opções exclusivas da função:
     * + offset: Número do registro inicial da pesquisa;
     * + limit	: Quantidade de registros retornados no caso de uma consulta;
     * Qualquer outra chave informada serve para substituir em tempo de execução as opções padrão de conexão.
     * - IMPORTANTE SOBRE DATAS!!!!!
     * Campos do tipo date/time quando utilizando o driver padrão mssql, devido a uma conversão que o PDO realizava,
     * não serão retornados como exibidos ao realizar a consulta direto no banco. É necessário converter para
     * varchar no formato de retorno desejado.
     * - IMPORTANTE SOBRE FORMATO UNICODE!!!!!
     * Campos no formato n[text,varchar,etc] não funcionam com o driver padrão mssql no linux. Só funciona no
     * windows, e é um mal funcionamento. Para resolver, ou se elimina o uso desse formato ou realiza-se a
     * conversão para o formato comum equivalente
     * @return QueryResultset
     * @throws \ErrorException
     */
    public function runQuery($_query, QueryOptions $_options = null) {
        $eStart = Connection::microtimeFloat();

        $erro = null;

        $colCount = -1;
        $rowCount = -1;
        $fullRowCount = -1;
        $id = -1;

        $query = trim($_query," ;\n\t\r");

        if(empty($query)) {
            $status = false;
            $res = null;
            $erro = new QueryException("Cannot run an empty query", 1, E_ERROR, __FILE__, __LINE__);
        } elseif(Connection::$forceErrorStatus === true || (is_string(Connection::$forceErrorStatus) && preg_match('/^'.Connection::$forceErrorStatus.'/i',$query))) {
            $status = false;
            $rowCount = -1;
            $colCount = -1;
            $fullRowCount = -1;
            $id = -1;
            $res = null;
            $erro = new QueryException("Forced error through Connection::\$forceErrorStatus option", 2, E_ERROR, __FILE__, __LINE__);
        } else {
            if(!is_null($_options) && $_options->getLimit() > 0) {
                if(is_numeric($_options->getOffset())) {
                    $offset = $_options->getOffset();
                } else {
                    $offset = 0;
                }

                $ordPos = strripos($query, 'ORDER BY');

                if($ordPos !== false && $ordPos > 0) {
                    $orderBy  = substr($query, $ordPos);

                    $query = substr($query, 0, $ordPos-1);
                    $query = preg_replace('/^SELECT((\040|\n|\r|\t)*?(DISTINCT))?/i','\\0 dense_rank() OVER ('.$orderBy.') AS rownum, ', $query);

                    $md5 = md5(uniqid());
                    $resultsTable = "#results_{$md5}";
                    $queryTotal = "SELECT * \nINTO {$resultsTable} \nFROM (\n" . $query . "\n) AS A \nORDER BY rownum;";

                    if($this->utf8Convert) {
                        $queryTotalFinal = utf8_decode($queryTotal);
                    } else {
                        $queryTotalFinal = $queryTotal;
                    }

                    $resTot = $this->query($queryTotalFinal);

                    if($resTot == true) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $fullRowCount = $this->rowsAffected();

                        $paginationFields = '';
                        $paginationJoins = '';
                        $distinct = '';
                        $groupBy = '';

                        if(count($_options->getPaginationJoins())) {
                            foreach($_options->getPaginationJoins() as $join) {
                                if(trim($join->getColumns()) != '') {
                                    $paginationFields .= ", \n{$join->getColumns()}";
                                }
                                $paginationJoins .= " \n{$join->getJoinStatement()}";

                                if(!is_null($join->getGroupBy()) && trim($join->getGroupBy()) != '') {
                                    $groupBy = (trim($groupBy) == '' ? "GROUP BY " : ', ') . trim($join->getGroupBy(), ', \t\n\r\0\x0B');
                                }
                            }

                            if(trim($groupBy) == '') {
                                $distinct = 'DISTINCT';
                            }
                            $paginationFields = rtrim($paginationFields, ', \t\n\r\0\x0B');
                        }

                        $query = "SELECT {$distinct} res.*{$paginationFields} \nFROM {$resultsTable} AS res {$paginationJoins} \nWHERE res.rownum BETWEEN (" .($offset + 1) . ") AND (".($offset + $_options->getLimit()).") {$groupBy} ORDER BY res.rownum ASC;";
                        //echo '<pre>'.$queryTotal.'<br/>';
                        //exit($query);
                    } else {
                        $status = false;
                        /** @noinspection PhpUndefinedMethodInspection */
                        $erro = new QueryException($this->error(), 4, E_ERROR, __FILE__, __LINE__);
                    }
                } else {
                    $status = false;
                    $erro = new QueryException("In order for the query to return something it must be ordered", 3, E_ERROR, __FILE__, __LINE__);
                }
            }

            $res = null;

            if(!isset($status)) {
                $qStart = Connection::microtimeFloat();

                if($this->utf8Convert) {
                    $queryFinal = utf8_decode($query);
                } else {
                    $queryFinal = $query;
                }

                $res = $this->query($queryFinal, [], []);

                $qEnd = Connection::microtimeFloat();

                if(is_resource($res)) {
                    $status = true;
                    $erro = null;

                    $colCount = $this->numFields($res);
                    if(preg_match('/^SELECT(.+)INTO/is',$query)) {
                        $rowCount = $this->rowsAffected($res);
                    } else {
                        $rowCount = $this->numRows($res);
                    }
                } elseif($res == true) {
                    $status = true;
                    $erro = null;
                    $colCount = -1;

                    $rowCount = $this->rowsAffected();

                    $preg1 = preg_match('/^INSERT((\040|\n|\r|\t)*?)INTO/i',$query);
                    preg_match_all('/(INSERT[\040|\n|\r|\t]*?INTO)/i',$query,$cQuery);
                    if($preg1 && count($cQuery[0]) == 1) {
                        $id = $this->insertId();
                    }
                } else {
                    $res = null;
                    $queryRet = (isset($queryTotalFinal) ? "{$queryTotalFinal}\n": '') . $queryFinal;
                    $erro = new QueryException("{$this->error()}\nQuery: {$queryRet}", 5, E_ERROR, __FILE__, __LINE__);
                    $status = false;
                }
            }
        }

        $stm = new QueryResultset($this,$res,$status,$rowCount,$colCount,$fullRowCount,$id, (!is_null($_options) ? $_options->getResultsetParams() : []));

        if(!is_null($erro)) {
            $stm->setErrorInfo($erro->getMessage());
        }
        $stm->queryString = isset($queryTotal) ? $queryTotal."\n\n".$query : $query;
        if(isset($qEnd) && isset($qStart)) {
            $stm->queryTime = $qEnd - $qStart;//Seconds
        }
        $stm->executionTime = Connection::microtimeFloat() - $eStart;

        if($status == false) {
            Connection::errorHandler($erro);
        }

        return $stm;
    }

    public function getOpenTransactions()
    {
        return $this->transactionOpen;
    }

    public function getOpenTransactionByName($_transactionName)
    {
        return isset($this->transactionOpen[$_transactionName]) ? $this->transactionOpen[$_transactionName] : null;
    }

    /**
     * Open a new transaction on the database
     * @param mixed $_tName
     * @return boolean
     */
    public function openTransaction($_tName = null) {
        $return = false;

        if(!isset($this->transactionOpen[$_tName])) {
            $res = self::runQuery("BEGIN TRANSACTION {$_tName}");
            if($res->getStatus()) {
                if(!is_null($_tName)) {
                    $this->transactionOpen[$_tName] = true;
                } else {
                    $this->transactionOpen[] = true;
                }
            }
            $return = $res->getStatus();
        } else {
            Connection::errorHandler(new TransactionException("There is already an open transaction named \"{$_tName}\""));
        }

        return $return;
    }

    /**
     * Faz o rollback da transação
     * @param mixed $_tName
     * @return bool
     * @throws \Exception
     */
    public function rollbackTransaction($_tName = null) {
        $flag = false;

        if(count($this->transactionOpen) > 0) {
            $res = self::runQuery("IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION {$_tName}");
            $flag = $res->getStatus();
            if($flag) {
                if(!is_null($_tName)) {
                    unset($this->transactionOpen[$_tName]);
                } else {
                    self::resetTransactions();
                }
            }
        }

        return $flag;
    }

    /**
     * Faz o commit da transação
     * @param mixed $_tName
     * @return boolean
     */
    public function commitTransaction($_tName = null) {
        $flag = false;

        if(count($this->transactionOpen) > 0) {
            $res = self::runQuery("COMMIT TRANSACTION {$_tName}");
            $flag = $res->getStatus();
            if($flag) {
                if(!is_null($_tName)) {
                    unset($this->transactionOpen[$_tName]);
                } else {
                    Connection::resetTransactionCount();
                }
            }
        }

        return $flag;
    }

    public function resetTransactions()
    {
        $this->transactionOpen = [];
    }

    /**
     * Executa uma transação em partes, dividindo as execuções de cada parte para evitar um timeout do driver
     *
     * @param array $_queries - Array de queries a serem executadas
     * @param TransactionOptions $_options
     * - Array de opções para a execução da função. As seguintes chaves são de opções exclusivas da função:
     * + numTentativas    : Quantidade de tentivas que o script deve executar quando encontrar um deadlock;
     * + delay            : Tempo que o script deve aguardar para executar novamente em caso de deadlock;
     * + debugQueries    : Coloca todas as queries envolvidas no processo em uma variável, mesmo elas sendo
     * executadas separadamente, para poder verificar possíveis erros;
     * + keepId            : Informa um Índice no array de queries que deve ser guardado para utilizar em
     * outras queries no decorrer da execução;
     * + keepIdVault    : Mapeamento de onde serão gravados os ids gerados, para utilizar em transações com
     * grande número de operações nas mesmas tabelas. É necessÃ¡rio utilizar o parametro keepId com o indice 'map'
     * setado com o array de mapeamento de qual indice das consultas sera guardado em cada variavel;
     * Qualquer outra chave informada serve para substituir em tempo de execução as opções padrão de conexão.
     * Obs: Esse trecho de sql "SET @insertID = SCOPE_IDENTITY(); SET @INSERTED = @INSERTED + CAST(@insertID as VARCHAR)+',';" precisa
     * ser executado junto com cada operação de insert no caso de desejar retornar os ids inseridos.
     * Caso deseje utilizar o id inserido é necessário utilizar esse trecho também, e colocar a variável @insertID no local que deseja receber o valor.
     * + returnObject    : Se verdadeiro retorna no fim da execução o objeto MultiTransactionResult
     * @return TransactionResultset
     */
    public function transaction(array $_queries, TransactionOptions $_options)
    {
        $dados = ['data' => false, 'label' => '', 'codigos' => [], 'errors' => []];

        $numTentativas = $_options->getAttempts();
        $delay = $_options->getDelay();
        $keepId = $_options->getKeepId();
        $keepIdVault = $_options->getKeepIdVault();
        $insertID = null;
        $codigos = '';
        $debug = $_options->isDebug();
        $valoresGuardados = [];
        $logAllErrors = $_options->isLogAllErrors();
        $cmdPrintPrct = $_options->isCmdPrintPrct();

        $currentDate = new \DateTime();
        $data = $currentDate->format('Ymd');
        $hora = $currentDate->format('H:i:s');

        $errors = [];

        $query_completa = '';
        $qtd = count($_queries);
        $executed = 0;
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $cmdColors = $cmdPrintPrct ? new \Colors\Color() : null;

        if($qtd > 0)
        {
            $beginTransaction = 'BEGIN TRANSACTION';

            if($debug)
                $query_completa = $beginTransaction;

            self::runQuery($beginTransaction);

            $status = true;

            $extraDeclaredVars = '';
            $extraSetVars = '';
            $keepIdVaultAlterado = false;

            if(!is_null($keepIdVault)) {
                foreach($keepIdVault as $vault) {
                    $extraDeclaredVars .= "DECLARE {$vault['name']} {$vault['type']};\n";
                    if(!empty($vault['value']))
                        $extraSetVars .= "SET {$vault['name']} = {$vault['value']};\n";
                }
            }

            $queryStr ="	BEGIN
        DECLARE @NumTentativas TINYINT;
        DECLARE @MaxPermitido TINYINT;
        DECLARE @insertID INT;
        DECLARE @Err INT;
        DECLARE @STBOL VARCHAR(5);
        DECLARE @ERRDESC VARCHAR(MAX);
        DECLARE @INSERTED VARCHAR(MAX);
        %s
        SET @NumTentativas = 0;
        SET @MaxPermitido = %s;%s
        %s

        TentaNovamente%s:
        SET @NumTentativas += 1;

        BEGIN TRY
            %s
            SET @Err = 0;
            SET @STBOL = 'TRUE';
            SET @ERRDESC = 'Query OK!';
        END TRY
        BEGIN CATCH
            SET @Err = @@ERROR

            IF XACT_STATE()=-1 BEGIN
                SET @STBOL = 'FALSE';
                IF @Err = 1205 BEGIN
                    SET @ERRDESC = 'DEADLOCK Error';
                END
                ELSE BEGIN
                    SET @ERRDESC = ERROR_MESSAGE();
                END

                ROLLBACK TRANSACTION;
            END
            ELSE BEGIN
                IF @Err = 1205 BEGIN
                    WAITFOR DELAY '%s';

                    IF @NumTentativas <= @MaxPermitido BEGIN
                        GOTO TentaNovamente%s;
                    END
                    ELSE BEGIN
                        SET @STBOL = 'FALSE';
                        SET @ERRDESC = 'DEADLOCK Error';
                        /*ROLLBACK TRANSACTION;*/
                    END
                END
                ELSE BEGIN
                    SET @STBOL = 'FALSE';
                    SET @ERRDESC = ERROR_MESSAGE();
                    /*ROLLBACK TRANSACTION;*/
                END
            END
        END CATCH
        SELECT @STBOL+'|'+CAST(@Err as VARCHAR)+'|'+@ERRDESC+'|'+@INSERTED+'|'+CAST(@insertID as VARCHAR) as 'STATUS';
    END";

            $queriesAudit = [];
            $auditValStr = "('%s','%s','%s','%s','%s',%d,'%s','%s','%s')";

            foreach($_queries as $key => $_query)
            {
                if($keepIdVaultAlterado && !is_null($keepIdVault)) {
                    $keepIdVaultAlterado = false;
                    $extraSetVars = '';
                    foreach($keepIdVault as $vault) {
                        if(!empty($vault['value']))
                            $extraSetVars .= "SET {$vault['name']} = {$vault['value']};\n";
                    }
                }

                $auditTable = [];
                $auditavel = preg_match("/OUTPUT/i", $_query) && preg_match("/#AuditTempTable_(?:[A-Za-z0-9]+)/", $_query, $auditTable);

                $query = sprintf($queryStr, $extraDeclaredVars, $numTentativas, (!is_null($insertID) ? "\n\t\t{$insertID}" : ''), $extraSetVars, $key, $_query, $delay, $key);

                if(Connection::$forceErrorStatus === true || (is_string(Connection::$forceErrorStatus) && preg_match('/^'.Connection::$forceErrorStatus.'/i',trim($_query, " ;\n\t\r")))) {
                    $stm = new QueryResultset($this, null, false, -1, -1, -1, -1);
                } else {
                    $stm = self::runQuery($query);
                    $executed++;
                }

                if($cmdPrintPrct) {
                    $prct = number_format(($executed*100)/$qtd,2,'.','');

                    echo $cmdColors("{$prct}%\r")->bg_blue;

                    if($prct == 100) {
                        echo "\n";
                    }
                }

                if($debug) {
                    $query_completa .= "\n\n" . $stm->queryString;
                }

                $verificador = 0;
                if($stm->getStatus()) {
                    $reg = $stm->fetch();
                    $tmp = explode('|',$reg['STATUS']);
                    $verificador = $tmp[0] == 'TRUE' ? 1 : 2;
                    /*if($verificador == 2) {
                        echo '<pre>'. print_r($tmp[2],true)."\n";
                        print_r($query);
                    }*/
                    //$dados['label'] = $tmp[2];
                    if($verificador == 2 && trim($tmp[2]) != '') {
                        $errors[] = $tmp[2];
                    }
                    $codigos .= $tmp[3];

                    if(!isset($keepId['map']) && ((is_array($keepId) && in_array($key,$keepId)) || $key == $keepId)) {

                        $insertID = "SET @insertID = ".intval($tmp[4]).";";

                    } elseif(isset($keepId['map']) && isset($keepId['map'][$key])) {
                        if(is_array($keepId['map'][$key])) {
                            foreach($keepId['map'][$key] as $keyMap) {
                                $keepIdVault[$keyMap]['value'] = intval($tmp[4]);
                                if($keepIdVault[$keyMap]['return']) {
                                    $valoresGuardados[] = intval($tmp[4]);
                                }
                            }
                        } else {
                            $keepIdVault[$keepId['map'][$key]]['value'] = intval($tmp[4]);
                            if($keepIdVault[$keepId['map'][$key]]['return']) {
                                $valoresGuardados[] = intval($tmp[4]);
                            }
                        }
                        $keepIdVaultAlterado = true;
                    }


                }

                if($verificador === 1 && $auditavel && count($auditTable) > 0) {
                    $queryTemp = "SELECT * FROM {$auditTable[0]};";
                    $resTemp = self::runQuery($queryTemp);
                    if($resTemp->getStatus()) {
                        if ($resTemp->rowCount() > 0) {
                            $reg = $resTemp->fetch();

                            $atTable = $reg['AT_TABLE'];
                            unset($reg['AT_TABLE']);
                            $atRecId = $reg['AT_RECID'];
                            unset($reg['AT_RECID']);
                            $atName = $reg['AT_NAME'];
                            unset($reg['AT_NAME']);
                            $atUserId = $reg['AT_USERID'];
                            unset($reg['AT_USERID']);

                            foreach ($reg as $col => $val) {
                                if(substr($col,0,3) != '_i_' && $val != $reg['_i_'.$col]) {
                                    $queriesAudit[] = sprintf($auditValStr, $atName, $hora, $data, 'U', $atTable, $atRecId, $col, $val, $atUserId);
                                }
                            }
                        }
                    } else {
                        $dados['label'] = 'An execution error ocurred! Try again';

                        $rollbackTransaction = 'IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION';
                        if($debug) {
                            $query_completa .= "\n\n" . $rollbackTransaction;
                        }

                        self::runQuery($rollbackTransaction);
                        $status = false;

                        Connection::errorHandler(new TransactionException("Query Error: {$queryTemp}", 2, E_ERROR, __FILE__, __LINE__));
                    }
                }

                if(in_array($verificador, [0,2]))
                {
                    $dados['label'] = 'An execution error ocurred! Try again';

                    /*if($verificador == 0)
                    {*/
                    $rollbackTransaction = 'IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION';
                    if($debug)
                        $query_completa .= "\n\n".$rollbackTransaction;

                    self::runQuery($rollbackTransaction);
                    //}
                    $status = false;

                    if($verificador == 2 && $logAllErrors) {
                        /** @noinspection PhpUndefinedVariableInspection */
                        Connection::errorHandler(new TransactionException("Query Error: {$query}\n\nMessage: {$tmp[2]}", 3, E_ERROR, __FILE__, __LINE__));
                    }

                    break;
                }
            }

            if($status && count($queriesAudit) > 0 && !is_null($_options->getAuditTable())) {
                $auditQuery = "INSERT INTO {$_options->getAuditTable()} (AT_NAME, AT_TIME, AT_DATE, AT_OP, AT_TABLE, AT_RECID, AT_FIELD, AT_CONTENT, AT_USERID) VALUES ".implode(',', $queriesAudit);

                $key = count($_queries);
                $query = sprintf($queryStr, $extraDeclaredVars, $numTentativas, (!is_null($insertID) ? "\n\t\t{$insertID}" : ''), $extraSetVars, $key, $auditQuery, $delay, $key);

                $stm = self::runQuery($query);

                if($debug) {
                    $query_completa .= "\n\n" . $stm->queryString;
                }

                $verificador = 0;
                if($stm->getStatus()) {
                    $reg = $stm->fetch();
                    $tmp = explode('|',$reg['STATUS']);
                    $verificador = $tmp[0] == 'TRUE' ? 1 : 2;
                    /*if($verificador == 2) {
                        echo '<pre>'. print_r($tmp[2],true)."\n";
                        print_r($query);
                    }*/
                    //$dados['label'] = $tmp[2];
                    if($verificador == 2 && trim($tmp[2]) != '') {
                        $errors[] = $tmp[2];
                    }
                    $codigos .= $tmp[3];
                }

                if(in_array($verificador, [0,2]))
                {
                    $rollbackTransaction = 'IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION';
                    if($debug)
                        $query_completa .= "\n\n".$rollbackTransaction;

                    self::runQuery($rollbackTransaction);

                    $status = false;

                    $dados['label'] = 'An execution error ocurred! Try again';
                    if($verificador == 2 && $logAllErrors) {
                        /** @noinspection PhpUndefinedVariableInspection */
                        Connection::errorHandler(new TransactionException("Query Error: {$query}\n\nMessage: {$tmp[2]}", 1, E_ERROR, __FILE__, __LINE__));
                    }
                }
            }

            if($status)
            {
                $commitTransaction = 'COMMIT TRANSACTION';

                if($debug) {
                    $query_completa .= "\n\n" . $commitTransaction;
                }

                $commit = self::runQuery($commitTransaction);

                if($commit->getStatus())
                {
                    $dados['data'] = true;
                    $dados['label'] = 'Query OK!';
                    $dados['codigos'] = explode(',',trim($codigos,', '));
                    $dados['valoresGuardados'] = $valoresGuardados;
                }
                else
                {
                    $dados['label'] = 'Execution error';
                    $dados['errors'] = $errors;
                }
            }
        } else {
            Connection::errorHandler(new TransactionException("To run a transaction there must be one statement at least", 1, E_ERROR, __FILE__, __LINE__));
        }

        if($debug) {
            $dados['consulta'] = $query_completa;
            $dados['keepId'] = $keepId;
            $dados['keepIdVault'] = $keepIdVault;
            $dados['valoresGuardados'] = $valoresGuardados;
        }

        return new TransactionResultset($dados);
    }

    /**
     * @internal
     */
    public function __toString()
    {
        return __CLASS__;
    }

    public function isConvertionToUtf8Needed()
    {
        return $this->utf8Convert;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }
}