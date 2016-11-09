<?php
namespace DatabaseLayer\Src\Engine;

use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\Exception\ConnectionException;
use DatabaseLayer\Src\Exception\EngineException;
use DatabaseLayer\Src\Exception\InvalidConfigurationFileException;
use DatabaseLayer\Src\Exception\InvalidConfigurationParamException;

class EngineFactory
{
    public static function load($_conName = null)
    {
        $conName = trim($_conName) == '' || is_null($_conName) ? $_conName : 'default';

        $parse = self::parseConfiguration($conName, Connection::$configurationFile);
        if(is_array($parse)) {
            /**
             * @var EngineInterface $engine
             */
            $engine = $parse['engine'];
            if($engine->isExtensionLoaded()) {
                $engine->connect($parse['config']);
                if(!$engine->isOK()) {
                    Connection::errorHandler(new ConnectionException("Connection to server failed", 1, E_ERROR, __FILE__, __LINE__));
                }
            } else {
                Connection::errorHandler(new EngineException("The extension {$engine->getExtension()} isn't loaded", 1, E_ERROR, __FILE__, __LINE__));
            }
        } else {
            $engine = new InvalidEngine([]);
        }

        return $engine;
    }

    public static function parseConfiguration($_conName, $_configurationFile)
    {
        $ret = false;

        if(file_exists($_configurationFile)) {
            /** @noinspection PhpIncludeInspection */
            $dbConfiguration = include($_configurationFile);

            if(isset($dbConfiguration[$_conName])) {
                if (is_array($dbConfiguration[$_conName])) {
                    if (!empty($dbConfiguration[$_conName][Connection::PARAM_ENGINE])) {
                        if (class_exists($dbConfiguration[$_conName][Connection::PARAM_ENGINE])) {
                            $engine = new $dbConfiguration[$_conName][Connection::PARAM_ENGINE]();
                            if ($engine instanceof EngineInterface) {
                                $ret = ['engine' => $engine, 'config' => $dbConfiguration[$_conName]];
                            }
                        } else {
                            Connection::errorHandler(new InvalidConfigurationParamException("\$dbConfiguration['engine'] must be an instance of EngineInterface", 4, E_ERROR, __FILE__, __LINE__));
                        }
                    } else {
                        Connection::errorHandler(new InvalidConfigurationParamException("\$dbConfiguration['engine'] must be set and not empty", 3, E_ERROR, __FILE__, __LINE__));
                    }
                } else {
                    Connection::errorHandler(new InvalidConfigurationParamException("\$dbConfiguration must be a multidimensional array", 2, E_ERROR, __FILE__, __LINE__));
                }
            } else {
                Connection::errorHandler(new InvalidConfigurationParamException("Provided connection name not found inside configuration", 1, E_ERROR, __FILE__, __LINE__));
            }
        } else {
            Connection::errorHandler(new InvalidConfigurationFileException("Path to supplied configuration file doesn't exist", 1, E_ERROR, __FILE__, __LINE__));
        }

        return $ret;
    }
}