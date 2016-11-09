<?php
namespace DatabaseLayer\Src\Statement;

class PreparedStatement
{
    private $constructTime      = null;
    private $parseTime          = null;

    private $query              = null;
    private $values             = [];
    private $tokens             = [];
    private $types              = [];
    private $tableNamePattern   = '/^((([a-z0-9_]+)\.([a-z0-9_]+)\.([a-z0-9_]+)(\s?(([as]{2})?(\s[a-z0-9_]+)?)?)?)||([#]([#])?([a-z0-9_])+))$/i';
    private $tokenPattern       = '/(?:[V])?%(?:\d+\$)?[+-]?(?:[ 0]|\'.{1})?-?(?:\d(?:\.\d+)?)*[bcdeEufFgGosxX]/';
    private $tokenExtract       = '/^([V])?%(\d+\$)?([+-])?([ 0]|\'.{1})?(-)?(\d(?:\.\d+)?)*([bcdeEufFgGosxX])$/';

    private $typeList = [
        'string'    => ['s'],
        'numeric'   => ['s'],
        'int'       => ['d','u','c','o','x','X','b'],
        'float'     => ['g','G','e','E','f','F']
    ];

    private $typePatterns = [
        'string'    => '[\w*\s-]',
        'int'       => '\d',
        'numeric'   => '\d',
        'float'     => '(?:(?:(?:[0]{1}|[1-9](?:[0-9])*)(?:\.(?:[0-9])+)?))'
    ];

    public function __construct($_query = null) {
        $eStart = self::_microtimeFloat();

        if(!is_null($_query) && !empty($_query)) {
            $this->query = $_query;
            if(preg_match_all($this->tokenPattern, $this->query, $matches)) {

                if(is_array($matches) && count($matches[0])) {
                    //exit('<pre>'.print_r($matches, true));

                    $namedParams = [];
                    $tokens = [];

                    foreach($matches[0] as $token) {
                        preg_match($this->tokenExtract, $token, $extracted);
                        list($full, $permiteVazio, $name, $flag, $padding, $signed, $value, $type) = $extracted;

                        $permiteVazio = (trim($permiteVazio) != '');
                        if(!empty($name)) {
                            $index = intval(str_replace('$', '', $name));
                            $namedParams[$index-1] = $index;

                            $temp = [
                                'permiteVazio' => $permiteVazio,
                                'full'      => $full,
                                'name'      => $name,
                                'index'     => $index - 1,
                                'flag'      => $flag,
                                'padding'   => $padding,
                                'signed'    => $signed,
                                'value'     => $value,
                                'type'      => $type
                            ];

                            if(isset($this->tokens[$name]))
                                $this->tokens[$name][] = $temp;
                            else
                                $this->tokens[$name] =  [$temp];
                        } else {
                            $tokens[] =  [
                                'permiteVazio' => $permiteVazio,
                                'full'      => $full,
                                'flag'      => $flag,
                                'padding'   => $padding,
                                'signed'    => $signed,
                                'value'     => $value,
                                'type'      => $type
                            ];
                        }
                    }
                    asort($namedParams);
                    $indexCount = count($namedParams);

                    if($indexCount > 0) {
                        if($namedParams !== array_values($namedParams)) {
                            throw new \ErrorException("Tokens nomeados devem seguir a ordem númerica iniciando em 1!", 0, E_ERROR, __FILE__, __LINE__);
                        }
                    }

                    foreach($tokens as $token) {
                        $this->tokens[$indexCount++] =  $token;
                    }
                }
            }
        } else
            throw new \ErrorException('Não é possível preparar uma instrução vazia.', 0, E_WARNING, __FILE__, __LINE__);

        $this->constructTime = self::_microtimeFloat() - $eStart;
    }

    private function _parse($_validate = true) {
        $eStart = self::_microtimeFloat();

        $qtdTokens = count($this->tokens);
        $qtdValues = count($this->values);
        if($qtdTokens != $qtdValues) {
            throw new \ErrorException("A quantidade de valores ({$qtdValues}) é diferente da quantidade de tokens ({$qtdTokens})!", 0, E_ERROR, __FILE__, __LINE__);
        }

        $query          = $this->query;
        $values         = $this->values;
        $types          = $this->types;
        $typeList       = $this->typeList;
        $self           = $this;

        if($_validate) {
            $i = 0;

            array_walk($this->tokens, function($token) use ($values, &$query, $self, $_validate, &$i, $types, $typeList) {
                if(isset($token[0])) {
                    foreach($token as $t) {
                        $self->_validateValues($types[$i], $t, $values[$i], $i);
                        $query = $self::_replaceToken($query, $t['full'], $self->_implodeValues($t['type'], $values[$i]));
                    }
                } else {
                    $self->_validateValues($types[$i], $token, $values[$i], $i);
                    $query = $self->_replaceToken($query, $token['full'], $self->_implodeValues($token['type'], $values[$i]));
                }

                $i++;
            });
        } else {
            $i = 0;
            array_walk($this->tokens, function($token) use ($values, &$query, $self, &$i) {
                if(isset($token[0])) {
                    foreach($token as $t) {
                        $query = $self::_replaceToken($query, $t['full'], $self->_implodeValues($t['type'], $values[$i]));
                    }
                } else {
                    $query = $self::_replaceToken($query, $token['full'], $self->_implodeValues($token['type'], $values[$i]));
                }

                $i++;
            });
        }

        $this->parseTime = self::_microtimeFloat() - $eStart;

        return $query;
    }

    public function _replaceToken($_format, $_token, $_value) {
        $search = preg_quote($_token);
        return preg_replace("/{$search}/", $_value, $_format, 1);
    }

    public function _validateValues($_typesValue, $_t, $_value, $_i) {
        $patterns = $this->typePatterns;

        if($_typesValue == 'float') {
            if($_t['value'] != '') {
                list($dec, $prec) = explode('.', $_t['value']);

                if(!is_null($prec)) {
                    $patterns['float'] = '(?:(?:(?:[0]{1}|[1-9](?:[0-9])*)(?:\.(?:[0-9]{'.strlen($prec).'})+)?))';
                }
            }
            $length = '';
        } else {
            $length = $_t['value'] != '' && intval($_t['value']) > 0 ? '{'.$_t['value'].'}' : '*';
        }

        $pattern = '/^('.$patterns[$_typesValue].')'.$length.'$/';

        if(!in_array($_t['type'], $this->typeList[$_typesValue]) || !preg_match($pattern, $_value) || (!$_t['permiteVazio'] && trim($_value) == '')) {
            throw new \ErrorException("O valor do índice {$_i}({$_value}:$_typesValue) não corresponde ao tipo esperado pelo token {$_t['full']}!", 0, E_ERROR, __FILE__, __LINE__);
        }
    }

    public function _implodeValues($_tokenType, $_value) {
        if($_tokenType == 's' && is_array($_value))
            $value = "'".implode("','", $_value);
        elseif($_tokenType == 's')
            $value = "'{$_value}'";
        else
            $value = $_value;

        return $value;
    }

    public function addInt($_value) {
        $this->values[] = $_value;
        $this->types[] = 'int';
        return $this;
    }

    public function addNumeric($_value) {
        $this->values[] = $_value;
        $this->types[] = 'numeric';
        return $this;
    }

    public function addString($_value) {
        $this->values[] = $_value;
        $this->types[] = 'string';
        return $this;
    }

    public function addFloat($_value) {
        $this->values[] = $_value;
        $this->types[] = 'float';
        return $this;
    }

    public function preview($_validate = false) {
        return $this->_parse($_validate);
    }

    public function exec($_connectionOptions = []) {
        Connection::open();
        return Connection::sQuery($this->_parse(true), $_connectionOptions);
    }

    /**
     * Retorna o tempo atual em segundos
     * @return float
     */
    private function _microtimeFloat() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}