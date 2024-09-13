# AI Services

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

## What?

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for a simple WordPress support assistant chatbot that can be disabled if not needed. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

## Why?

* A centralized AI infrastructure **facilitates user choice**. Users may prefer certain AI services over other ones, and for many common tasks, either of the popular AI services is suitable. Having a common API regardless of the AI service allows leaving the choice to the user, rather than the plugin author.
* Since the centralized AI infrastructure comes with a common API that works the same for every AI service, it means **plugin developers don't have to spend as much time familiarizing themselves with different services**, at least when it comes to simple tasks. For tasks where certain services may have advantages over others, there is still flexibility to focus on a specific AI service.
* It also means **no more reinventing the wheel**: Since most AI services do not provide PHP SDKs for their APIs, many times this means WordPress plugins that want to leverage AI have to implement their own layer around the service's API. Not only is that time consuming, it also distracts from working on the actual (AI driven) features that the plugin should offer to its users. In fact this directly facilitates the user choice aspect mentioned, as having APIs for various AI services already provided means you can simply make those available to your plugin users.
* Having central AI infrastructure available **unlocks AI capabilities for smaller plugins or features**: It may not be worth the investment to implement a whole AI API layer for a simple AI driven feature, but when you already have it available, it can lead to more plugins (and thus more users) benefitting from AI capabilities.
* Last but not least, a central AI infrastructure means **users will only have to configure the AI API once**, e.g. paste their API keys only in a single WordPress administration screen. Without central AI infrastructure, every plugin has to provide its own UI for pasting API keys, making the process more tedious for site owners the more AI capabilities their site uses.

## Examples

```php
// Generate the answer to a prompt in PHP code.
if ( ai_services()->has_available_services() ) {
	$service = ai_services()->get_available_service();
	try {
		$result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
```

```js
// Generate the answer to a prompt in JavaScript code.
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices() ) {
	const service = getAvailableService();
	try {
		const result = await service.generateText( 'What can I do with WordPress?' );
	} catch ( error ) {
		// Handle the error.
	}
}
```

```sh
// Generate the answer to a prompt using WP-CLI.
wp ai-services generate-text 'What can I do with WordPress?'
```

You can also use a specific AI service, if you have a preference, for example the `google` service:
```php
// Generate the answer to a prompt using a specific AI service, in PHP code.
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
```

```sh
# Generate the answer to a prompt using a specific AI service, using the REST API via cURL.
curl 'https://example.com/wp-json/ai-services/v1/services/google:generate-text' \
  -H 'Content-Type: application/json' \
  --data-raw '{"content":"What can I do with WordPress?"}'
```

## Disclaimer

This plugin is still in its very early stages, with a limited feature set. Consider it early access at this point, there are lots of enhancements to add and polishing to do. That's why your feedback is much appreciated!

## Getting started

1. `composer install`
2. `composer prefix-dependencies`
3. `npm install`

## Useful commands

* `composer prefix-dependencies`: Prefixes the production dependencies and regenerates the autoloader class map. You must run this after a PHP dependency used in plugin production code has been updated.
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
* `npm run format-js`: Automatically fixes JavaScript code detected.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
