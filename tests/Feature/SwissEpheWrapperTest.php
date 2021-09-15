<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Nastasin\SwissEpheWrapper\SwissEpheWrapper;
use Carbon\Carbon;

class SwissEpheWrapperTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCommandTest()
    {
        $time = Carbon::createFromFormat('U', '1528797148')->setTimezone('UTC');
        $options = [
            'dateTime' => $time
        ];

        $planetary = new SwissEpheWrapper($options);

        $expectedArray = [
            0 => [
                0 => 57.2880505,
                1 => 0.9561232
            ],
            1 => [
                0 => 37.7137134,
                1 => 14.6396868
            ],
            $time
        ];


        $this->assertEquals($planetary->get(), collect([collect($expectedArray)]));
    }

    /**
     * @throws \Exception
     */
    public function testCommandSecondTest()
    {
        $time = Carbon::parse('1.1.2002')->setTimezone('UTC');
        $options = [
            'dateTime' => $time,
            'lat'       => 56.94,
            'lng'       => 24.10,
        ];

        $planetary = new SwissEpheWrapper($options);

        $expectedArray = [
            0 => [
                0 => 256.5091027,
                1 => 1.0188384
            ],
            1 => [
                0 => 97.2372097,
                1 => 14.5007212
            ],
            $time
        ];

        $this->assertEquals($planetary->get(), collect([collect($expectedArray)]));
    }

    public function testConsecutiveDays()
    {
        $optionsMain = [
            'dateTime' => Carbon::parse('2020-feb-23')->setTimezone('UTC'),
            'lat'      => 56.9496,
            'lng'      => 24.0978,
            'step'     => 4 //this equals like a command executed four times in a row
        ];

        $serviceMain = new SwissEpheWrapper($optionsMain);

        $optionsMain['dateTime'] = Carbon::parse($optionsMain['dateTime'])->subDay(); //offset

        $fourConsecutiveDays = collect([1, 2, 3, 4])->map(function ($item) use ($optionsMain) {

            $newOpts = $optionsMain;
            $newOpts['step'] = 1;
            $newOpts['dateTime'] = Carbon::parse($optionsMain['dateTime'])->addDays($item);

            return (new SwissEpheWrapper($newOpts))->get();
        })->flatten(1);

        $this->assertEquals($serviceMain->get(), $fourConsecutiveDays);

    }
}
