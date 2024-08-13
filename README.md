# WP OOP Plugin Lib Example

This example plugin provides a reference and potential starting point for using the [WP OOP Plugin Lib library](https://github.com/felixarntz/wp-oop-plugin-lib). The plugin includes the library in a way that prevents conflicts with other plugins that may include it, by prefixing the classes.

The example plugin contains very minimal functionality (adding a few options and an admin page) just to show how some of the foundational pieces of the library can be set up.

However, it's fast and easy to remove this functionality and instead use the codebase from this repository as a starting point for creating a new plugin that uses the [WP OOP Plugin Lib library](https://github.com/felixarntz/wp-oop-plugin-lib).

## Using this project as a starter plugin

In order to create a new plugin based on this foundation, perform the following steps:

1. Copy all files into your new plugin's directory.
2. Perform the following _case-sensitive_ replacements globally across all files (using your plugin's slug and its different variants, per the examples below):
    * Replace `wp_oop_plugin_lib_example` with `my_plugin`
    * Replace `wp-oop-plugin-lib-example` with `my-plugin`
    * Replace `WP_OOP_Plugin_Lib_Example` with `My_Plugin`
    * Replace `WP_OOP_PLUGIN_LIB_EXAMPLE` with `MY_PLUGIN`
    * Replace `WP OOP Plugin Lib Example` with `My Plugin`
    * Replace `wpOopPluginLibExample` with `myPlugin`
    * Replace `wpoopple_` with `myplugin_`
    * Replace `wpoopple-` with `myplugin-`
    * Replace `Vendor_NS` with `My_Namespace`
    * Replace `vendor-ns` with `my-github`
    * Replace `The plugin description.` with `My plugin does something useful.`
    * Replace `The Plugin Author` with `Real Author Name`
    * Replace `https://the-plugin-author.com` with `https://real-author-website.com`
3. Remove any parts of the PHP codebase that you don't need.
4. Update the `README.md` file as needed.
5. (Optional) If your plugin does not require any custom JavaScript, remove the `src` directory and the JavaScript-specific infrastructure files (e.g. `.github/workflows/js-lint.yml`, `.prettierignore`, `webpack.config.js`).

## Getting started

1. `composer install`
2. `composer prefix-dependencies`
3. `npm install`

## Useful commands

* `composer prefix-dependencies`: Prefixes the production dependencies and regenerates the autoloader class map. You must run this after a PHP dependency used in plugin production code has been updated.
* `composer lint`: Checks the code with PHP_CodeSniffer.
* `composer format`: Automatically fixes code problems detected by PHPCodeSniffer, where possible.
* `composer phpmd`: Checks the code with PHPMD.
* `composer phpstan`: Checks the code with PHPStan.
* `npm run wp-env start`: Starts the built-in development environment (typically available at `http://localhost:8888/`).
* `npm run wp-env stop`: Stops the built-in development environment.
* `npm run test-php`: Runs the PHPUnit tests for a regular (single) WordPress site using the built-in development environment.
* `npm run test-php-multisite`: Runs the PHPUnit tests for a WordPress multisite using the built-in development environment.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
