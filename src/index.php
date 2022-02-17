<?php

require '../vendor/autoload.php';

use Carbon\Carbon;
use Nastasin\SwissEpheWrapper\SwissEpheWrapper;


#example

$time = Carbon::now()->setTimezone('UTC');

$options = [
    SwissEpheWrapper::OPT_DATETIME => $time,
    SwissEpheWrapper::OPT_LNG => 24.105078, //for Riga/Europe
    SwissEpheWrapper::OPT_LAT => 56.946285,
    SwissEpheWrapper::OPT_GEOPOS    => 'geopos',
    SwissEpheWrapper::OPT_PARAMS    => 'l',
    SwissEpheWrapper::OPT_PLIST     => SwissEpheWrapper::MOON,
    SwissEpheWrapper::OPT_STEP      => 2,
    SwissEpheWrapper::OPT_STEPSIZE  => 1,
    SwissEpheWrapper::OPT_ELEVATION => 20
];


$planetary = new SwissEpheWrapper($options);

$planetary->get();
