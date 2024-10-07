# AI Services

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

![AI Services settings screen showing text input fields for API credentials](https://github.com/user-attachments/assets/da7ba9e2-b9bd-4d03-aebc-1a42c33689be)

**Disclaimer:** The AI Services plugin is still in its very early stages, with a limited feature set. As long as it is in a `0.x.y` version, expect occasional breaking changes. Consider the plugin early access at this point, as there are lots of enhancements to add and polishing to do. A crucial part of that is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the third party services offer in a uniform way. That's why your feedback is much appreciated!

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
		$result = $service
      ->get_model( array( 'feature' => 'my-test-feature' ) )
      ->generate_text( 'What can I do with WordPress?' );
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
		const result = await service.generateText(
      'What can I do with WordPress?',
      { feature: 'my-test-feature' }
    );
	} catch ( error ) {
		// Handle the error.
	}
}
```

```sh
// Generate the answer to a prompt using WP-CLI.
wp ai-services generate-text 'What can I do with WordPress?' --feature=my-test-feature
```

You can also use a specific AI service, if you have a preference, for example the `google` service:
```php
// Generate the answer to a prompt using a specific AI service, in PHP code.
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$result = $service
      ->get_model( array( 'feature' => 'my-test-feature' ) )
      ->generate_text( 'What can I do with WordPress?' );
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

## Installation and usage

You can install [the latest built release from the WordPress plugin directory](https://wordpress.org/plugins/ai-services/), which in the long term will be the recommended way to use the plugin. Keep in mind that any `0.x.y` releases are considered early access and may contain breaking changes.

Alternatively, especially in this early development stage of the plugin, feel free to test the plugin by cloning the GitHub repository. Afterwards, please run the following commands to make sure the dependencies are installed and the plugin build is complete:

```
git clone https://github.com/felixarntz/ai-services.git wp-content/plugins/ai-services
cd wp-content/plugins/ai-services
composer install
composer prefix-dependencies
npm install
npm run build
```

If you want to test the plugin in its own built-in development environment, please follow the instructions in the [code contributing guidelines](./CONTRIBUTING.md#getting-started-with-writing-code).

Once the AI Services plugin is installed and activated, you can configure the plugin with your AI service credentials using the _Settings > AI Services_ screen in the WP Admin menu.

## Using the plugin

Once the plugin is active, you will find a new _Settings > AI Services_ submenu in the WordPress administration menu. In there, you can configure your AI service API keys. Once you have configured at least one (valid) API key, you should see a small "Need help?" button in the lower right throughout WP Admin, which exposes the built-in WordPress assistant chatbot. This is the only user-facing feature of the plugin, effectively as a simple proof of concept for the APIs that the plugin infrastructure provides.

Please refer to the [documentation](./docs/README.md) for instructions on how you can actually use the AI capabilities of the plugin in your own projects.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
