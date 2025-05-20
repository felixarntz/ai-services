# Development Workflow

This file outlines the key commands and steps for setting up and working with the AI Services WordPress plugin development environment.

## Getting Started

To set up the plugin for the very first time, you'll need to have [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) installed. For the built-in development environment and PHPUnit tests, you'll also need [Docker](https://www.docker.com/).

Run the following commands to set up the project:

```sh
composer install
npm install
npm run build
```

## Building the Plugin

The following commands are relevant to build the plugin assets:

- `composer prefix-dependencies`: Prefixes the production dependencies and regenerates the autoloader class map. This is run automatically after `composer install` and `composer update`.
- `npm run build`: Builds the JavaScript and CSS assets.

## Linting and Code Quality

The following commands are available for checking code quality and formatting:

- `composer lint`: Checks the PHP code with [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/).
- `composer format`: Automatically fixes PHP code problems detected by PHPCodeSniffer, where possible.
- `composer phpmd`: Checks the PHP code with [PHPMD](https://github.com/phpmd/phpmd).
- `composer phpstan`: Checks the PHP code with [PHPStan](https://github.com/phpstan/phpstan).
- `npm run lint-js`: Checks the JavaScript code with [ESLint](https://eslint.io/) and [Prettier](https://prettier.io/). Note that this is using a WordPress coding standards compliant [fork of Prettier called `wp-prettier`](https://www.npmjs.com/package/wp-prettier).
- `npm run format-js`: Formats the JavaScript code using the Prettier requirements.
- `npm run lint-css`: Checks the CSS code with [Stylelint](https://stylelint.io/).
- `npm run format-css`: Formats the CSS code using the Stylelint requirements.
- `npm run lint-md`: Checks Markdown docs.

## Running Tests

The following commands allow running PHPUnit tests using the built-in environment:

- `npm run test-php`: Runs the PHPUnit tests for a regular (single) WordPress site.
- `npm run test-php-multisite`: Runs the PHPUnit tests for a WordPress multisite.

Running tests requires the development environment to be running (see below).

## Development Environment

You can access the built-in development environment using [wp-env](https://www.npmjs.com/package/@wordpress/env):

- `npm run wp-env start`: Starts the environment (typically available at `http://localhost:8888/`).
- `npm run wp-env stop`: Stops the environment.
