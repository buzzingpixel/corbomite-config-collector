<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\configcollector;

use DirectoryIterator;

class Factory
{
    public static function collector(): Collector
    {
        return new Collector(new self());
    }

    public function makeCollector(): Collector
    {
        return self::collector();
    }

    public static function directoryIterator(string $path): DirectoryIterator
    {
        return new DirectoryIterator($path);
    }

    public function makeDirectoryIterator(string $path): DirectoryIterator
    {
        return self::directoryIterator($path);
    }
}
