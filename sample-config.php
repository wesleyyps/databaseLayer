<?php
return [
    'default' => [
        \DatabaseLayer\Src\Connection::PARAM_HOST => 'some.server.address',
        \DatabaseLayer\Src\Connection::PARAM_USER => 'the.user',
        \DatabaseLayer\Src\Connection::PARAM_PWD => 'the.pass',
        \DatabaseLayer\Src\Connection::PARAM_OPTIONS => [
            /*
             * Persistent connection not allowed in this case
             */
            \DatabaseLayer\Src\Engine\MSSQLEngine::PARAM_NEWLINK => false
        ],
        \DatabaseLayer\Src\Connection::PARAM_ENGINE => '\DatabaseLayer\Src\Engine\MSSQLEngine',
        /*
         * Tells connection if it needs to encode utf8 strings automatically, because sometimes you just can't change
         * the database parameters in php.ini or in the database
         */
        \DatabaseLayer\Src\Connection::PARAM_UTF8ENC => true,
        /*
         * If PARAM_DBTIMEZONE isn't provided, the connection will assume that the server's timezone is the same as
         * the php server timezone, and if these values are different this may be a problem
         */
        \DatabaseLayer\Src\Connection::PARAM_DBTIMEZONE => 'Etc/GMT+3'
    ]
];