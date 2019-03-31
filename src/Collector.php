<?php

declare(strict_types=1);

namespace corbomite\configcollector;

use const DIRECTORY_SEPARATOR;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function is_array;
use function json_decode;

class Collector
{
    /** @var Factory $factory */
    private $factory;
    /** @var string $appBasePath */
    private $appBasePath;

    public function __construct(Factory $factory, string $appBasePath)
    {
        $this->factory     = $factory;
        $this->appBasePath = $appBasePath;
    }

    public function __invoke(string $extraKeyName) : array
    {
        return $this->collect($extraKeyName);
    }

    public function getPathsFromExtraKey(string $extraKeyName) : array
    {
        $items = [];

        $vendorIterator = $this->factory->makeDirectoryIterator(
            $this->appBasePath . DIRECTORY_SEPARATOR . 'vendor'
        );

        foreach ($vendorIterator as $fileInfo) {
            if ($fileInfo->isDot() || ! $fileInfo->isDir()) {
                continue;
            }

            $providerIterator = $this->factory->makeDirectoryIterator(
                $fileInfo->getPathname()
            );

            foreach ($providerIterator as $providerFileInfo) {
                if ($providerFileInfo->isDot() ||
                    ! $providerFileInfo->isDir()
                ) {
                    continue;
                }

                $thisItem = $this->getExtraKeyFromPath(
                    $providerFileInfo->getPathname(),
                    $extraKeyName
                );

                if (! $thisItem) {
                    continue;
                }

                $items[] = $providerFileInfo->getPathname() .
                    DIRECTORY_SEPARATOR .
                    $thisItem;
            }
        }

        $item = $this->getExtraKeyFromPath($this->appBasePath, $extraKeyName);

        if ($item) {
            $items[] = $this->appBasePath . DIRECTORY_SEPARATOR . $item;
        }

        return $items;
    }

    public function getExtraKeyAsArray(string $extraKeyName) : array
    {
        $array = [];

        $vendorIterator = $this->factory->makeDirectoryIterator(
            $this->appBasePath . DIRECTORY_SEPARATOR . 'vendor'
        );

        foreach ($vendorIterator as $fileInfo) {
            if ($fileInfo->isDot() || ! $fileInfo->isDir()) {
                continue;
            }

            $providerIterator = $this->factory->makeDirectoryIterator(
                $fileInfo->getPathname()
            );

            foreach ($providerIterator as $providerFileInfo) {
                if ($providerFileInfo->isDot() ||
                    ! $providerFileInfo->isDir()
                ) {
                    continue;
                }

                $thisArray = $this->getExtraKeyFromPath(
                    $providerFileInfo->getPathname(),
                    $extraKeyName
                );
                $thisArray = is_array($thisArray) ? $thisArray : [];

                $array = array_merge($array, $thisArray);
            }
        }

        $local = $this->getExtraKeyFromPath($this->appBasePath, $extraKeyName);
        $local = is_array($local) ? $local : [];

        $array = array_merge($array, $local);

        return $array;
    }

    public function collect(string $extraKeyName) : array
    {
        $config = [];

        $vendorIterator = $this->factory->makeDirectoryIterator(
            $this->appBasePath . DIRECTORY_SEPARATOR . 'vendor'
        );

        foreach ($vendorIterator as $fileInfo) {
            if ($fileInfo->isDot() || ! $fileInfo->isDir()) {
                continue;
            }

            $providerIterator = $this->factory->makeDirectoryIterator(
                $fileInfo->getPathname()
            );

            foreach ($providerIterator as $providerFileInfo) {
                if ($providerFileInfo->isDot() ||
                    ! $providerFileInfo->isDir()
                ) {
                    continue;
                }

                $config = array_merge($config, $this->collectFromPath(
                    $providerFileInfo->getPathname(),
                    $extraKeyName
                ));
            }
        }

        $config = array_merge(
            $config,
            $this->collectFromPath($this->appBasePath, $extraKeyName)
        );

        return $config;
    }

    public function getExtraKeyFromPath(
        string $path,
        string $extraKeyName,
        $default = null
    ) {
        $composerJsonPath = $path . DIRECTORY_SEPARATOR . 'composer.json';

        if (! file_exists($composerJsonPath)) {
            return null;
        }

        $json = json_decode(file_get_contents($composerJsonPath), true);

        return $json['extra'][$extraKeyName] ??
            $default;
    }

    public function collectFromPath(string $path, string $extraKeyName) : array
    {
        $filePath = $this->getExtraKeyFromPath($path, $extraKeyName, 'asdf');

        $configFilePath = $path . DIRECTORY_SEPARATOR . $filePath;

        if (! file_exists($configFilePath)) {
            return [];
        }

        $configInclude = include $configFilePath;

        return is_array($configInclude) ? $configInclude : [];
    }
}
