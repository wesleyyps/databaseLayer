<?php
namespace DatabaseLayer\Src\Helpers;

use DatabaseLayer\Src\Connection;
use DatabaseLayer\Src\Exception\DateTimeException;

class DateTime extends \DateTime
{
    /**
     * @var string
     */
    private $originalFormat;

    /**
     * @param string $format Format accepted by date().
     * @param string $time String representing the time.
     * @param \DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
     * @return DateTime
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        $ext_dt = new static('now', null, $format);
        if(!is_null($timezone)) {
            $ext_dt->setTimezone($timezone);
        }
        $ext_dt->setTimestamp(parent::createFromFormat($format, $time, $timezone)->getTimestamp());
        return $ext_dt;
    }

    public function __construct($time = 'now', \DateTimeZone $timezone = null, $_format = null)
    {
        $this->originalFormat = $_format;
        parent::__construct($time, $timezone);
    }

    public function format($_format = null)
    {
        return parent::format(!is_null($_format) ? $_format : $this->originalFormat);
    }

    public static function fitToFormat($_date, $_format)
    {
        $format = preg_replace(
            ['/Y/',   '/m|(?<!\\\)d|H|i|s/', '/u/',   '/D/',   '/j/',     '/N/',      '/S/',   '/w/'],
            ['\d{4}', '\d{2}',               '\d{6}', '\w{3}', '\d{1,2}', '[1-7]{1}', '\w{2}', '[0-6]{1}'],
            $_format
        );
        $match = preg_match('/'.$format.'/', $_date, $matches);
        if($match) {
            $ret = $matches[0];
        } else {
            $ret = false;
            Connection::errorHandler(new DateTimeException("The value does not match the format", 0, E_ERROR, __FILE__, __LINE__));
        }

        return $ret;
    }
}