# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.2] - 2019-03-31
### Fixed
- Fixed an issue where collectFromPath would throw errors when trying to include directories or non-php files

## [1.1.1] - 2019-03-03
### Fixed
- Fixed an issue where the local project config wouldn't win over package config

## [1.1.0] - 2019-02-27
### Changed
- Removed need for APP_BASE_PATH to be defined. Will automatically determine path if not set

## [1.0.4] - 2019-01-12
### Changed
- Made all methods on the collector public

## [1.0.3] - 2019-01-12
### Added
- Added method getPathsFromExtraKey to collector

## [1.0.2] - 2019-01-11
### Added
- Added method to collector to get extra key as array

## [1.0.1] - 2019-01-11
### Added
- Added additional factory methods

## [1.0.0] - 2019-01-11
### New
- Initial Release
