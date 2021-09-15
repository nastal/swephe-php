<?php

require '../vendor/autoload.php';

use Carbon\Carbon;
use Nastasin\SwissEpheWrapper\SwissEpheWrapper;


#example

$time = Carbon::now()->setTimezone('UTC');

//default bodies Sun and Moon
$options = [
    SwissEpheWrapper::OPT_DATETIME => $time,
    SwissEpheWrapper::OPT_LNG => 24.105078, //for Riga/Europe
    SwissEpheWrapper::OPT_LAT => 56.946285
];


$planetary = new SwissEpheWrapper($options);

dd($planetary->get());
