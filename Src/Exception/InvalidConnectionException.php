<?php
namespace DatabaseLayer\Src\Exception;

class InvalidConnectionException extends \ErrorException implements ExceptionInterface
{
    public function __construct($_conName, $_file, $_line)
    {
        parent::__construct("The required connection \"{$_conName}\" isn't open!", 0, E_ERROR, $_file, $_line);
    }
}