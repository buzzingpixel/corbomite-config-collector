<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\configcollector;

use ReflectionClass;
use DirectoryIterator;
use Composer\Autoload\ClassLoader;

class Factory
{
    public static function collector(): Collector
    {
        if (defined('APP_BASE_PATH')) {
            return new Collector(new Factory(), APP_BASE_PATH);
        }

        $reflection = new ReflectionClass(ClassLoader::class);

        return new Collector(
            new Factory(),
            dirname($reflection->getFileName(), 3)
        );
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
