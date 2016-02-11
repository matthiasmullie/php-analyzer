# [cauditor](https://www.cauditor.org) PHP analyzer

[![Build status](https://api.travis-ci.org/cauditor/php-analyzer.svg?branch=master)](https://travis-ci.org/cauditor/php-analyzer)
[![Code coverage](http://img.shields.io/codecov/c/github/cauditor/php-analyzer.svg)](https://codecov.io/github/cauditor/php-analyzer)
[![Code quality](http://img.shields.io/scrutinizer/g/cauditor/php-analyzer.svg)](https://scrutinizer-ci.com/g/cauditor/php-analyzer)
[![Latest version](http://img.shields.io/packagist/v/cauditor/analyzer.svg)](https://packagist.org/packages/cauditor/analyzer)
[![Downloads total](http://img.shields.io/packagist/dt/cauditor/analyzer.svg)](https://packagist.org/packages/cauditor/analyzer)


![Pretty stats](https://www.cauditor.org/assets/img/banner.png)


Setting it up is a ridiculously easy 2-step process:


## Installation

### 1. Composer

Simply add a dependency on cauditor/analyzer to your composer.json file
if you use [Composer](https://getcomposer.org/) to manage the dependencies of
your project:

```sh
composer require cauditor/analyzer --dev
```

This will make this library available in your CI server.


### 2. CI build

Add this to your *.travis.yml*'s `after_success` statements:

**.travis.yml**
```yml
after_success:
  - vendor/bin/cauditor
```

It'll instruct Travis CI to generate the metrics & submit them to [cauditor.org](https://www.cauditor.org).

This should also work on other CI providers, as long as you make sure
`composer install --dev` is run so this client gets installed.


### 3. Look at those pretty metrics!

Point your browser to [https://www.cauditor.org/you/your-project](https://www.cauditor.org/you/your-project)
and look at the results!


## Configuration

Add a *.cauditor.yml* file to the root of your project. Available configuration
options (and their defaults) are:


**.cauditor.yml**
```yml
# path where metrics data will be exported to
build_path: build/cauditor
# folders to be excluded when analyzing code
exclude_folders: [tests, vendor]
```

*Note that, in addition to whatever is configured, folders 'vendors', '.git' &
'.svn' are always excluded.*
