<?php

declare(strict_types=1);

namespace corbomite\configcollector;

use Composer\Autoload\ClassLoader;
use DirectoryIterator;
use ReflectionClass;
use function defined;
use function dirname;

class Factory
{
    public static function collector() : Collector
    {
        if (defined('APP_BASE_PATH')) {
            return new Collector(new Factory(), APP_BASE_PATH);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = new ReflectionClass(ClassLoader::class);

        return new Collector(
            new Factory(),
            dirname($reflection->getFileName(), 3)
        );
    }

    public function makeCollector() : Collector
    {
        return self::collector();
    }

    public static function directoryIterator(string $path) : DirectoryIterator
    {
        return new DirectoryIterator($path);
    }

    public function makeDirectoryIterator(string $path) : DirectoryIterator
    {
        return self::directoryIterator($path);
    }
}
