# Changelog


## [1.2.4] - 2016-04-13
### Added
- Submit weighed averages


## [1.2.3] - 2016-03-24
### Added
- Allow supplying different API target (e.g. http instead of https)

### Changed
- Clarify success & error output


## [1.2.2] - 2016-03-21
### Added
- Fail when we risk incomplete data
- Keep retrying on analyze fail


## [1.2.1] - 2016-03-18
### Added
- Submits 'noc'


## [1.2.0] - 2016-03-15
### Added
- Submits default branch
- Submits average, min & max metrics

### Changed
- Extract commit hashes from (changed) API response

### Fixed
- Project-wide instability is now sum, like all other metrics


## [1.1.1] - 2016-03-07
### Fixed
- Fix previous commit hash for latest commit
- When analyzing specific commits, do full clone


## [1.1.0] - 2016-03-02
### Added
- Allows analyzing specific branch
- Allows analyzing specific commits

### Changed
- Do shallow clone when only last commit is needed
- Don't copy entire repo for all, just stash code
- Config adapts to changing config files


## [1.0.2] - 2016-02-29
### Changed
- Rolled own PDepend generator instead of converting

### Fixed
- Fixed 'he', which was converted incorrectly
- Fixed project-wide 'i', whose value kept adding up


## [1.0.1] - 2016-02-29
### Fixed
- Conditionally show success/error msg
- Make ci-sniffer pick up repo when other --repo is specified


## [1.0.0] - 2016-02-25
### Added
- Run pdepend for metrics
- Convert pdepend XML to cauditor JSON
- Submit to cauditor API


[1.0.0]: https://github.com/cauditor/php-analyzer/compare/cdcffeec68ccee59efdee5dd056ea5456b6e4b09...1.0.0
[1.0.1]: https://github.com/cauditor/php-analyzer/compare/1.0.0...1.0.1
[1.0.2]: https://github.com/cauditor/php-analyzer/compare/1.0.1...1.0.2
[1.1.0]: https://github.com/cauditor/php-analyzer/compare/1.0.2...1.1.0
[1.1.1]: https://github.com/cauditor/php-analyzer/compare/1.1.0...1.1.1
[1.2.0]: https://github.com/cauditor/php-analyzer/compare/1.1.1...1.2.0
[1.2.1]: https://github.com/cauditor/php-analyzer/compare/1.2.0...1.2.1
[1.2.2]: https://github.com/cauditor/php-analyzer/compare/1.2.1...1.2.2
[1.2.3]: https://github.com/cauditor/php-analyzer/compare/1.2.2...1.2.3
[1.2.4]: https://github.com/cauditor/php-analyzer/compare/1.2.3...1.2.4
