# WP Starter Plugin

This starter plugin provides a headstart for writing object-oriented WordPress plugins following best practices for both PHP and JavaScript:

* On the PHP side, it serves as a reference and starting point for using the [WP OOP Plugin Lib library](https://github.com/felixarntz/wp-oop-plugin-lib), which facilitates best practices such as dependency injection in a WordPress context. The plugin includes the library in a way that prevents conflicts with other plugins that may include it, by prefixing the classes.
* On the JavaScript side, it provides scaffolding for a comprehensive UI application using React and WordPress components, following established Gutenberg patterns and best practices.

The starter plugin contains minimal functionality (adding a few options and an admin page) just to show how some of the foundational pieces of the PHP library and the JS infrastructure can be set up.

Chances are you may actually want to iterate on some of that starter code rather than removing it. However, it's also fast and easy to remove the demo functionality entirely and start from scratch.

## Creating a new plugin from this starter plugin

In order to create a new plugin based on this foundation, perform the following steps:

1. Copy all files into your new plugin's directory.
2. Perform the following _case-sensitive_ replacements globally across all files (using your plugin's slug and its different variants, per the examples below):
    * Replace `wp_starter_plugin` with `my_plugin`
    * Replace `wp-starter-plugin` with `my-plugin`
    * Replace `WP_Starter_Plugin` with `My_Plugin`
    * Replace `WP_STARTER_PLUGIN` with `MY_PLUGIN`
    * Replace `WP Starter Plugin` with `My Plugin`
    * Replace `wpStarterPlugin` with `myPlugin`
    * Replace `wpsp_` with `myplugin_`
    * Replace `wpsp-` with `myplugin-`
    * Replace `Vendor_NS` with `My_Namespace`
    * Replace `vendor-ns` with `my-github`
    * Replace `The plugin description.` with `My plugin does something useful.`
    * Replace `https://the-plugin.com` with `https://real-plugin-website.com`
    * Replace `The Plugin Author` with `Real Author Name`
    * Replace `https://the-plugin-author.com` with `https://real-author-website.com`
3. Rename the `wp-starter-plugin.php` file to `my-plugin.php`.
4. Remove any parts of the PHP codebase that you don't need.
5. Update the `README.md` file as needed.
6. (Optional) If your plugin does not require any custom JavaScript, remove the `src` directory and the JavaScript-specific infrastructure files (e.g. `.github/workflows/js-lint.yml`, `.prettierignore`, `webpack.config.js`).

## Getting started

1. `composer install`
2. `composer prefix-dependencies`
3. `npm install`

## Useful commands

* `composer prefix-dependencies`: Prefixes the production dependencies and regenerates the autoloader class map. This is run automatically after `composer install` and `composer update`.
* `composer lint`: Checks the PHP code with PHP_CodeSniffer.
* `composer format`: Automatically fixes PHP code problems detected by PHP_CodeSniffer, where possible.
* `composer phpmd`: Checks the PHP code with PHPMD.
* `composer phpstan`: Checks the PHP code with PHPStan.
* `npm run build`: Builds the JavaScript and CSS assets.
* `npm run wp-env start`: Starts the built-in development environment (typically available at `http://localhost:8888/`).
* `npm run wp-env stop`: Stops the built-in development environment.
* `npm run test-php`: Runs the PHPUnit tests for a regular (single) WordPress site using the built-in development environment.
* `npm run test-php-multisite`: Runs the PHPUnit tests for a WordPress multisite using the built-in development environment.
* `npm run lint-css`: Checks the CSS code.
* `npm run format-css`: Automatically fixes CSS code detected.
* `npm run lint-js`: Checks the JavaScript code.
* `npm run lint-md`: Checks Markdown docs.
* `npm run format-js`: Automatically fixes JavaScript code detected.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
