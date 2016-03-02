# Changelog


## [1.1.0] - 2016-03-02
### Added
- Allows analyzing specific branch
- Allows analyzing specific commits

### Changed
- Do shallow clone when only last commit is needed
- Don't copy entire repo for all, just stash code


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
