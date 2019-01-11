<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use corbomite\di\Di;
use corbomite\configcollector\Factory;
use corbomite\configcollector\Collector;

return [
    Factory::class => function () {
        return new Factory();
    },
    Collector::class => function () {
        return new Collector(Di::get(Factory::class));
    },
];
