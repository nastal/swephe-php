<?php

namespace Nastasin\SwissEpheWrapper;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use ReflectionClass;
use Exception;

//More about command line options: https://www.astro.com/cgi/swetest.cgi?arg=-h&p=0
//test tool: https://www.astro.com/swisseph/swetest.htm
//check e.g.: //-b27.06.2018 -n1 -s1 -fPlL -p0123456t -eswe -ut20:18:13 -sid1 -true -geopos56.94,24.10,0

class SwissEpheWrapper
{
    const HEAD = 'swetest -edir';
    const LOCAL_PATH = '/c-ephe-lib';

    const UTC = 'UTC';

    const SUN = 0;
    const MOON = 1;
    const MERCURY = 2;
    const VENUS = 3;
    const MARS = 4;
    const JUPITER = 5;
    const SATURN = 6;
    const RAHUKETU = 't';

    const PLANET_NAMES = [
        self::SUN => 'Sun',
        self::MOON => 'Moon',
        self::MERCURY => 'Mercury',
        self::VENUS => 'Venus',
        self::MARS => 'Mars',
        self::JUPITER => 'Jupiter',
        self::SATURN => 'Saturn',
        self::RAHUKETU => 'RahuKetu',
    ];

    const DEFAULT_LAT = 41.007222; //Firefield, Iowa, USA - used for tests
    const DEFAULT_LNG = -91.965833;

    private $options = [
        self::OPT_DATETIME  => '',
        self::OPT_LAT       => self::DEFAULT_LAT,
        self::OPT_LNG       => self::DEFAULT_LNG,
        self::OPT_GEOPOS    => self::OPT_GEOPOS,
        self::OPT_PARAMS    => 'ls',
        self::OPT_PLIST     => self::SUN . self::MOON,
        self::OPT_STEP      => 1,
        self::OPT_STEPSIZE  => 1,
        self::OPT_ELEVATION => 0
    ];

    /**
     * @Carbon\CarbonPeriod
     */
    private $dateRange;

    protected $path;

    const OPT_DATETIME = 'dateTime';
    const OPT_LAT = 'lat';
    const OPT_LNG = 'lng';
    const OPT_GEOPOS = 'geopos';
    const OPT_PARAMS = 'params';
    const OPT_PLIST = 'plist';
    const OPT_STEP = 'step';
    const OPT_STEPSIZE = 'stepsize';
    const OPT_ELEVATION = 'elevation';

    const DATE_PREFIX = '-b';
    const TIME_PREFXIX = '-ut';
    const STEP_DAY_PREFIX = '-n';
    const STEP_PREFIX = '-s';
    const PLANET_PREFIX = '-p';
    const OUTPUT_PREFIX = '-f';
    const PARAMS_PREFIX = '-eswe -sid1 -true -head';
    const HYPEN         = '-';
    const SPACE         = ' ';

    /**
     * SwissEpheWrapper constructor.
     * @param  array  $options
     * @throws \ReflectionException
     */
    public function __construct($options = [])
    {
        $reflector = new ReflectionClass(__CLASS__);
        $fn = $reflector->getFileName();
        $path = dirname($fn);

        $this->path = $path . self::LOCAL_PATH;

        putenv('PATH=' . $this->path);
        $this->setOptions($options);
    }

    /**
     * @throws \Exception;
     * @return Collection
     */
    public function get()
    {

        $served = array_map(function ($str) {
            $val = preg_split('/\s+/', trim($str));
            return array_map('floatval', $val);
        }, $this->comExec($this->getOptions()));

        $bodies = strlen($this->options['plist']);
        $bodiesCount = strpos($this->options['plist'], self::RAHUKETU) ? $bodies + 1 : $bodies;

        if (count($served) / $bodiesCount !== count($this->dateRange->toArray())) {
            throw new Exception('dateRange and served not equal');
        }

        $frameCollection = Collection::make($served)
            ->split(count($served) / $bodiesCount);

        return $this->appendDateRange($frameCollection);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @throws \Exception
     */
    public function setOptions(array $options)
    {
        $this->options[self::OPT_DATETIME] = $options[self::OPT_DATETIME] ?? Carbon::now();
        if ($this->options[self::OPT_DATETIME]->getTimezone()->getName() !== self::UTC) {
            throw new Exception('Timezone should be in UTC!');
        }

        $step = isset($options[self::OPT_STEP]) ? $options[self::OPT_STEP] : $this->options[self::OPT_STEP];
        $stepSize = isset($options[self::OPT_STEPSIZE]) ? $options[self::OPT_STEPSIZE] : $this->options[self::OPT_STEPSIZE];
        $this->dateRange = CarbonPeriod::create(
            Carbon::parse($this->options[self::OPT_DATETIME]),
            $stepSize . ' days',
            Carbon::parse($this->options[self::OPT_DATETIME])->addDays($step - 1)
        );

        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param $options
     * @return mixed
     */
    private function comExec($options)
    {
        $string[] = self::HEAD . $this->path; //head
        $string[] = self::DATE_PREFIX . (clone $options[self::OPT_DATETIME])->format('d.m.Y'); //date
        $string[] = self::TIME_PREFXIX . (clone $options[self::OPT_DATETIME])->format('H:i:s'); //time
        $string[] = self::STEP_DAY_PREFIX . $options[self::OPT_STEP] . self::SPACE . self::STEP_PREFIX . $options[self::OPT_STEPSIZE]; //step params (one day)
        $string[] = self::PLANET_PREFIX . $options[self::OPT_PLIST]; //body list
        $string[] = self::OUTPUT_PREFIX . $options[self::OPT_PARAMS]; //output
        $string[] = self::PARAMS_PREFIX; //other params
        $string[] = self::HYPEN .$options[self::OPT_GEOPOS] . '' .$options[self::OPT_LAT] . ',' .$options[self::OPT_LNG] . ',' .$options[self::OPT_ELEVATION]; //geopos

        $out = implode(' ', $string);
        exec($out, $output);
        return $output;
    }

    /**
     * @param $bodyList Collection
     * @return Collection
     */
    private function appendDateRange(Collection $bodyList)
    {
        $i = 0;
        $dateRange = $this->dateRange->toArray();
        foreach ($bodyList as $item) {
            $item->push($dateRange[$i]);
            $i++;
        }
        return $bodyList;
    }
}
