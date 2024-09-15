# Contributing to AI Services

Thank you for your interest in contributing to this plugin! At this point, it is still in an early development stage, but especially because of that feedback is much appreciated!

Just two general guidelines:
* All contributors are expected to follow the [WordPress Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/).
* All contributors who submit a pull request are agreeing to release their contribution under the [GPLv2+ license](https://github.com/felixarntz/ai-services/blob/main/LICENSE).

## Providing feedback

If you've already started testing the APIs in a WordPress plugin, or you've started experimenting with them, you may run into limitations, or you simply may have questions on how a certain part of the infrastructure is supposed to work. You may run into a bug, or you may think about another AI capability that you would like to see covered by this library. In any case, please let me know by [opening an issue](https://github.com/felixarntz/ai-services/issues/new/choose)!

## Contributing code

Pull requests are welcome! For little fixes, feel free to go right ahead and open one. For new features or larger enhancements, I'd encourage you to open an issue first where we can scope and discuss the change. Though of course in any case feel free to jump right into writing code! You can do so by [forking this repository](https://github.com/felixarntz/ai-services/fork) and later opening a pull request with your changes.

### Guidelines for contributing code

If you're interested in contributing code, please consider the following guidelines and best practices:

* All code must follow the [WordPress Coding Standards and best practices](https://developer.wordpress.org/coding-standards/), including documentation. They are enforced via the project's PHP_CodeSniffer configuration.
* All code must be backward-compatible with WordPress 6.0 and PHP 7.2.
* All code must pass the automated PHP code quality requirements via the project's PHPMD and PHPStan configuration.
* All functional code changes should be accompanied by PHPUnit tests.

### Getting started with writing code

For the linting tools to work, you'll need to have [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) installed on your machine. In order to make use of the built-in development environment including the ability to run the PHPUnit tests, you'll also need [Docker](https://www.docker.com/).

To set up the plugin for the very first time, please run the following:
```
composer install
composer prefix-dependencies
npm install
npm run build
```

The following commands are relevant to build the plugin:
* `composer prefix-dependencies`: Prefixes the PHP production dependencies and regenerates the autoloader class map. You must run this after a PHP dependency used in plugin production code has been updated.
* `npm run build`: Builds the JavaScript and CSS assets.

The following linting commands are available:

* `composer lint`: Checks the PHP code with [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/).
* `composer format`: Automatically fixes PHP code problems detected by PHPCodeSniffer, where possible.
* `composer phpmd`: Checks the PHP code with [PHPMD](https://github.com/phpmd/phpmd).
* `composer phpstan`: Checks the PHP code with [PHPStan](https://github.com/phpstan/phpstan).
* `npm run lint-js`: Checks the JavaScript code with [ESLint](https://eslint.org/) and [Prettier](https://prettier.io/).
* `npm run format-js`: Formats the JavaScript code using the Prettier requirements.
* `npm run lint-css`: Checks the CSS code with [Stylelint](https://stylelint.io/).
* `npm run format-css`: Formats the CSS code using the Stylelint requirements.

The following commands allow running PHPUnit tests using the built-in environment:

* `npm run test-php`: Runs the PHPUnit tests for a regular (single) WordPress site.
* `npm run test-php-multisite`: Runs the PHPUnit tests for a WordPress multisite.

You can access the built-in development environment using [wp-env](https://www.npmjs.com/package/@wordpress/env):

* `npm run wp-env start`: Starts the environment (typically available at `http://localhost:8888/`).
* `npm run wp-env stop`: Stops the environment.
