<?php
namespace DatabaseLayer\Src\Statement;

use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\Resultset\QueryResultset;

class NamedParamStatement
{
    /**
     * Parametros nomeados encontrados na classe
     * @var array
     */
    private $tokens = [];
    /**
     * Configurações da conexão
     * @var array
     */
    private $attributes;
    /**
     * Valores validados utilizados no bind
     * @var array
     */
    private $bindValues = [];
    /**
     * Query informada na criação do objeto
     * @var string
     */
    private $queryString;

    public function __construct($_query, array $_attr = [])
    {
        $this->attributes = $_attr;
        $this->queryString = trim($_query);

        if($this->queryString != '') {
            preg_match_all('/(?::([A-Za-z0-9_]+)+)/', $this->queryString, $matches);
            if (isset($matches[1]) && count($matches) > 0) {
                $this->tokens = array_unique($matches[1]);
            } else {
                self::displayError("Não foram encontrados marcadores para preparar a query!", __LINE__);
            }
        } else {
            self::displayError("Não é possível preparar uma query vazia!", __LINE__);
        }
    }

    public function bindInt($_param, $_value, $_length = null)
    {
        $this->bindValues[$_param] = intval(AntiInjection::soNumeros($_value, $_length));
        return $this;
    }

    public function bindNumber($_param, $_value, $_length = null)
    {
        $this->bindValues[$_param] = "'".AntiInjection::soNumeros($_value, $_length)."'";
        return $this;
    }

    public function bindFloat($_param, $_value, $_length = null, $_precision = null)
    {
        $this->bindValues[$_param] = AntiInjection::float($_value, $_length, $_precision);
        return $this;
    }

    public function bindString($_param, $_value)
    {
        $this->bindValues[$_param] = "'".AntiInjection::cleanValue($_value)."'";
        return $this;
    }

    public function bindVar($_param, $_value)
    {
        $this->bindValues[$_param] = '@'.AntiInjection::variavelDB($_value);
        return $this;
    }

    public function bindDate($_param, $_value, $_mask = 'Y-m-d H:i:s', $_default = '')
    {
        $this->bindValues[$_param] = "'".AntiInjection::dateTime($_value, $_mask, $_default)."'";
        return $this;
    }

    /**
     * @return string
     */
    public function parse()
    {
        $query = $this->queryString;

        $cBV = count($this->bindValues);
        $cTks= count($this->tokens);

        if($cBV >= $cTks) {
            $keys = array_keys($this->bindValues);
            $diff = array_diff($this->tokens, $keys);

            if(count($diff) == 0) {
                foreach($this->tokens as $tok) {
                    $query = str_replace(":{$tok}", $this->bindValues[$tok], $query);
                }
            } else {
                self::displayError("Os seguintes marcadores não foram informados: ".implode(', ', $diff), __LINE__);
            }
        } else {
            self::displayError("Quantidade de valores associados menor que a quantidade de marcadores esperados.", __LINE__);
        }

        return $query;
    }

    /**
     * @return QueryResultset
     */
    public function execute()
    {
        return Connection::query(self::parse(), $this->attributes);
    }
}