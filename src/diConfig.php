<?php

declare(strict_types=1);

use corbomite\configcollector\Collector;
use corbomite\configcollector\Factory;

return [
    Factory::class => static function () {
        return new Factory();
    },
    Collector::class => static function () {
        return Factory::collector();
    },
];
