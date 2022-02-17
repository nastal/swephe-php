#Simple swiss ephemeris wrapper

This package is a simple wrapper around swiss ephemeris test command
to gain planetary positions with '-sid1' option by default to apply Siderial (Lahiri Ayanamsha) calculations.

You can find more docs explaining options on [Swiss Ephemeris Test Page
](https://www.astro.com/swisseph/swetest.htm)

This repository includes following ephemeris files for a period 1800 AD – 2399 AD:

- sepl_18.se1 = Planetary file
- semo_18.se1 = Moon file
- seas_18.se1 = Main asteroid file
- swetest = executable file

#Requirements

This package were tested with builds based on Linux (Ubuntu) and PHP 7.4-fpm and PHP 8.0-fpm
Additional packages already included in composer file.

#Features

- Build-in step and step size options to get planetary positions for period with one execution.
- Accepts Carbon object with UTC, in result output you 
- By default calculates Sun and Moon positions.
- Accepts decimal Latitude and Longitude for geo position.
- Elevation level
- Outputs [Laravel collection](https://laravel.com/docs/master/collections) of mapped data

#Usage and code examples

```
use Carbon\Carbon;
use Nastasin\SwissEpheWrapper\SwissEpheWrapper;

//Sun and Moon longitude positions by default
$options = [
    SwissEpheWrapper::OPT_DATETIME => Carbon::now()->setTimezone('UTC'), //UTC required
    SwissEpheWrapper::OPT_LNG => 24.105078, //coordinates for city (Riga/Europe in example)
    SwissEpheWrapper::OPT_LAT => 56.946285
];

$planetary = new SwissEpheWrapper($options);

$planetary->get()
```

###outputs:

```
Illuminate\Support\Collection {#7 ▼
#items: array:1 [▼
    0 => Illuminate\Support\Collection {#5 ▼
      #items: array:3 [▼
        0 => array:2 [▼
          0 => 303.868487 //Sun longitude at present day
          1 => 1.0092834 //Sun speed longitude decimal (degrees/day)
        ]
        1 => array:2 [▼
          0 => 124.2552189 //Moon longitude at present day
          1 => 12.7868005 //Moon speed longitude decimal (degrees/day)
        ]
        2 => Carbon\Carbon @1645033465 { ▼
          ...
          date: 2022-02-16 17:44:25.800801 UTC (+00:00) //Carbon::now(at a moment of example)
        }
      ]
    }
]
}
```
---
With explained options.
Moon longitude for 2 consecutive days, starts from on 1 March 2022 12:00 UTC

```
use Carbon\Carbon;
use Nastasin\SwissEpheWrapper\SwissEpheWrapper;

$time = Carbon::parse('1-03-2022 12:00:00')->setTimezone('UTC');

$options = [
    SwissEpheWrapper::OPT_DATETIME => $time,
    SwissEpheWrapper::OPT_LNG => 24.105078, //for Riga/Europe
    SwissEpheWrapper::OPT_LAT => 56.946285,
    SwissEpheWrapper::OPT_GEOPOS    => 'geopos',
    SwissEpheWrapper::OPT_PARAMS    => 'l', //get only longitude (long. and long speed by default)
    SwissEpheWrapper::OPT_PLIST     => SwissEpheWrapper::MOON, //only Moon
    SwissEpheWrapper::OPT_STEP      => 2, //two consecutive days
    SwissEpheWrapper::OPT_STEPSIZE  => 1,
    SwissEpheWrapper::OPT_ELEVATION => 20 //20m above sea level
];


$planetary = new SwissEpheWrapper($options);

$planetary->get();
```

###outputs:

```
^ Illuminate\Support\Collection {#8 ▼
  #items: array:2 [▼
    0 => Illuminate\Support\Collection {#7 ▼
      #items: array:2 [▼
        0 => array:1 [▼
          0 => 300.5799517
        ]
        1 => Carbon\Carbon @1646136000 {#15 ▼
          ...
          date: 2022-03-01 12:00:00.0 UTC (+00:00)
        }
      ]
    }
    1 => Illuminate\Support\Collection {#5 ▼
      #items: array:2 [▼
        0 => array:1 [▼
          0 => 314.7017604
        ]
        1 => Carbon\Carbon @1646222400 {#16 ▼
          ...
          date: 2022-03-02 12:00:00.0 UTC (+00:00)
        }
      ]
    }
  ]
}
```