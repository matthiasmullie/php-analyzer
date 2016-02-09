# How to contribute


## Issues

When [filing bugs](https://github.com/cauditor/php-analyzer/issues/new),
try to be as thorough as possible:
* What version did you use?
* What did you try to do? ***Please post the relevant parts of your code.***
* What went wrong? ***Please include error messages, if any.***
* What was the expected result?


## Pull requests

Bug fixes and general improvements to the existing codebase are always welcome.
Don't forget to add your changes to the [changelog](CHANGELOG.md).


### Testing

Please include tests for every change or addition to the code.
To run the complete test suite:

```sh
vendor/bin/phpunit
```

When submitting a new pull request, please make sure that that the test suite
passes (Travis CI will run it & report back on your pull request.)


### Coding standards

All code must follow [PSR-2](http://www.php-fig.org/psr/psr-2/). Just make sure
to run php-cs-fixer before submitting the code, it'll take care of the
formatting for you:

```sh
vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests
```

Document the code thoroughly!


## License

Note that php-analyzer is MIT-licensed, which basically allows anyone to do
anything they like with it, without restriction.
