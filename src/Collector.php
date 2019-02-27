<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\configcollector;

class Collector
{
    private $factory;
    private $appBasePath;

    public function __construct(Factory $factory, string $appBasePath)
    {
        $this->factory = $factory;
        $this->appBasePath = $appBasePath;
    }

    public function __invoke(string $extraKeyName): array
    {
        return $this->collect($extraKeyName);
    }

    public function getPathsFromExtraKey(string $extraKeyName): array
    {
        $items = [];

        $item = $this->getExtraKeyFromPath($this->appBasePath, $extraKeyName);

        if ($item) {
            $items[] = $this->appBasePath . DIRECTORY_SEPARATOR . $item;
        }

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

                if ($thisItem) {
                    $items[] =$providerFileInfo->getPathname() .
                        DIRECTORY_SEPARATOR .
                        $thisItem;
                }
            }
        }

        return $items;
    }

    public function getExtraKeyAsArray(string $extraKeyName): array
    {
        $array = $this->getExtraKeyFromPath($this->appBasePath, $extraKeyName);
        $array = \is_array($array) ? $array : [];

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
                $thisArray = \is_array($thisArray) ? $thisArray : [];

                $array = array_merge($array, $thisArray);
            }
        }

        return $array;
    }

    public function collect(string $extraKeyName): array
    {
        $config = $this->collectFromPath($this->appBasePath, $extraKeyName);

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

        return isset($json['extra'][$extraKeyName]) ?
            $json['extra'][$extraKeyName] :
            $default;
    }

    public function collectFromPath(string $path, string $extraKeyName): array
    {
        $filePath = $this->getExtraKeyFromPath($path, $extraKeyName, 'asdf');

        $configFilePath = $path . DIRECTORY_SEPARATOR . $filePath;

        if (! file_exists($configFilePath)) {
            return [];
        }

        $configInclude = include $configFilePath;

        return \is_array($configInclude) ? $configInclude : [];
    }
}
