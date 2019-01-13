<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\configcollector;

use LogicException;

class Collector
{
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(string $extraKeyName): array
    {
        return $this->collect($extraKeyName);
    }

    public function getPathsFromExtraKey(string $extraKeyName): array
    {
        if (! defined('APP_BASE_PATH')) {
            throw new LogicException('APP_BASE_PATH must be defined');
        }

        $items = [];

        $item = $this->getExtraKeyFromPath(APP_BASE_PATH, $extraKeyName);

        if ($item) {
            $items[] = APP_BASE_PATH . DIRECTORY_SEPARATOR . $item;
        }

        $vendorIterator = $this->factory->makeDirectoryIterator(
            APP_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor'
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
        if (! defined('APP_BASE_PATH')) {
            throw new LogicException('APP_BASE_PATH must be defined');
        }

        $array = $this->getExtraKeyFromPath(APP_BASE_PATH, $extraKeyName);
        $array = \is_array($array) ? $array : [];

        $vendorIterator = $this->factory->makeDirectoryIterator(
            APP_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor'
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
        if (! defined('APP_BASE_PATH')) {
            throw new LogicException('APP_BASE_PATH must be defined');
        }

        $config = $this->collectFromPath(APP_BASE_PATH, $extraKeyName);

        $vendorIterator = $this->factory->makeDirectoryIterator(
            APP_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor'
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
