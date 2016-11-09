<?php
namespace Src;
/**
 * Tratamento de variáveis
 * @author Wesley Sousa <wesley.sousa@espro.org.br>
 * @package Core
 * @subpackage Libs/Helpers
 * @copyright 2012 Espro
 * @static
 */
class AntiInjection
{
    /**
     * Função para limpar strings antes de passar para o banco de dados
     * @param mixed $_value - Valor para tratar
     * @param boolean $_escapeQuotes - Se o método deve escapar aspas e caracteres não imprimiveis
     * @param string $_allowedHtmlTags - String com a lista de tags html para manter na hora da limpeza
     * @return mixed O valor tratado
     * @static
     */
    public static function clean($_value,$_escapeQuotes=true,$_allowedHtmlTags=null)
    {
        if(is_array($_value)) {
            $value = [];
            foreach($_value as $k => $v) {
                if(!is_array($v)) {
                    $value[$k] = AntiInjection::cleanValue($v, $_escapeQuotes, $_allowedHtmlTags);
                } else {
                    $value[$k] = AntiInjection::clean($v, $_escapeQuotes, $_allowedHtmlTags);
                }
            }
        } else {
            $value = AntiInjection::cleanValue($_value, $_escapeQuotes, $_allowedHtmlTags);
        }

        return $value;
    }

    /**
     * Mantem os numeros na string e verifica se a quantidade de digitos é válida
     * @param mixed $_val
     * @param int $_qtdDigitos
     * @param bool $_unsigned Se omite sinal de pos|neg
     * @return mixed
     */
    public static function soNumeros($_val,$_qtdDigitos = null, $_unsigned = true) {
        $val = preg_replace("/[^0-9".($_unsigned ? '' : '-')."]/", "", $_val);
        $pos = strpos($val, '-');
        if($pos !== false && $pos > 0) {
            $val = str_replace('-', '', $val);
        }

        if(!is_null($_qtdDigitos) && intval($_qtdDigitos) > 0)
            $value = preg_match('/^(-)?[0-9]{'.$_qtdDigitos.'}$/', $val) ? $val : '';
        else
            $value = $val;

        return trim($value);
      }

    public static function dateTime($_date, $_format = 'Y-m-d H:i:s', $_default = '') {
        if(preg_match("@^(?:(y)|m)(?:[/|-]?)(?:(y)|m)$@i", trim($_format), $matches)) {
            //exit('<pre>'.print_r($matches, true));
            $internalFormat = "{$_format}d";
            $internalData = "{$_date}01";
        } else {
            $internalFormat = $_format;
            $internalData = $_date;
        }

        $d = \DateTime::createFromFormat($internalFormat, $internalData);
        return ($d && $d->format($_format) == $_date) ? $_date : $_default;
    }

    /**
     * @internal
     */
    public function __toString()
    {
        return __CLASS__;
    }

    public static function email($_email) {
        //$email = trim($email);
        //return preg_match("@^([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+)?)*?[\@]([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[.]([0-9,a-z,A-Z]){2}([0-9,a-z,A-Z])?$@", $_email);
        $email = filter_var($_email, FILTER_VALIDATE_EMAIL);
        return $email === false ? '' : $email;
    }

    public static function cleanValue($_value, $_escapeQuotes=true, $_allowedHtmlTags=null, $_escapeNewLineChars = true) {
        $value = str_replace(' ', '-', $_value);//Substitui o travessão do word por um traço

        if($_escapeNewLineChars) {
            $value = trim(preg_replace('/(\t|\n|\r)/', ' ', $value));
        }
        //$value = trim(preg_replace('/\s(?=\s)/', '', $value));
        $value = trim(preg_replace('/\040(?=\040)/', '', $value));

        //Escape unicode chars
        $value = preg_replace("/%u201C|%u201D/", '"', $value);
        $value = preg_replace("/%u2018|%u2019/", "'", $value);
        $value = preg_replace("/%u2014|%u2013/", '-', $value);

        if($_escapeQuotes)
            $value = AntiInjection::escape($value);
        //$value = addslashes($value);
        $value = strip_tags($value,$_allowedHtmlTags);
        //$value = preg_replace(AntiInjection::mbSqlRegcase("/(%0a|%0d|Content-Type:|bcc:|^to:|cc:|Autoreply:|from|select|insert|delete|where|update|table|drop table|show tables|alter table|database|drop database|drop|destroy|union|TABLE_NAME|1=1|or 1|exec|INFORMATION_SCHEMA|like|COLUMNS|into|VALUES|#|--|\\\\)/"),"",$value);

        return $value;
    }

    public static function alphaNumeric($_value) {
        return preg_match('/^([a-zA-Z0-9_]+)$/', $_value) ? $_value : '';
    }

    public static function justAlpha($_value) {
        return trim(preg_replace("/[^a-zA-Z,]/", " ", $_value));
    }

    public static function float($_value, $_qtdDigitos = null, $_precisao = null) {
        $value = trim($_value);

        if(is_string($value)) {
            $coma = strpos($value, ',');
            $dot = strpos($value, '.');

            if(!($coma === false)) {
                if(!($dot === false)) {
                    if($coma > $dot) {
                        $value = str_replace('.', '', $value);
                        $value = str_replace(',', '.', $value);
                    } else {
                        $value = str_replace(',', '', $value);
                    }
                } else {
                    $value = str_replace(',', '.', $value);
                }
            }
        }

        if(preg_match('/^(?:(?:(?:[0]{1}|[1-9](?:[0-9])*)(?:.(?:[0-9])+)?))$/', $value)) {
            $verificaMaximo = !is_null($_qtdDigitos) && intval($_qtdDigitos) > 0;
            $verificaDigitos = !is_null($_precisao) && intval($_precisao) > 0;

            //$value = floatval($value);
            //$value = str_replace(',', '.', $value);

            if($verificaMaximo || $verificaDigitos) {
                $pos = strpos($value, '.');

                if(!($pos === false)) {
                    $float = explode('.', $value);
                } else {
                    $float = [0 => $value, 1 => ''];
                }

                if($verificaDigitos)
                    $value = strlen($float[1]) <= $_precisao ? $value : '';

                if($verificaMaximo) {
                    $value = strlen($float[0].$float[1]) <= $_qtdDigitos  ? $value : '';
                }
            }
        } else
            $value = '';

        return $value;
    }

    public static function dbVar($_value) {
        return preg_match('/^([\@]{1}([\w])+)$/', $_value) ? $_value : '';
    }

    public static function fileName($_value) {
        $replace_chars = [
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
            'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
            'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
            'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
        ];

        $f = strtr($_value, $replace_chars);
        // convert & to "and", @ to "at", and # to "number"
        $f = preg_replace(['/[\&]/', '/[\@]/', '/[\#]/'], ['-and-', '-at-', '-number-'], $f);
        $f = preg_replace('/[^(\x20-\x7F)]*/','', $f); // removes any special chars we missed
        $f = str_replace(' ', '-', $f); // convert space to hyphen
        $f = str_replace('\'', '', $f); // removes apostrophes
        $f = preg_replace('/[^\w\-\.]+/', '', $f); // remove non-word chars (leaving hyphens and periods)
        $f = preg_replace('/[\-]+/', '-', $f); // converts groups of hyphens into one

        return $f;
    }
}