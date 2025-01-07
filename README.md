# AI Services

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

| AI Services settings screen | AI Playground screen |
| ------------- | ------------- |
| ![The AI Services settings screen where users can paste their AI service credentials](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-1.png)  | ![The AI Playground where users can explore the AI capabilities of the different services](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-2.png)  |

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for an AI Playground screen to explore AI capabilities as well as a settings screen to configure AI service credentials. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

**Disclaimer:** The AI Services plugin is still in its early stages, with a limited feature set. As long as it is in a `0.x.y` version, there may be occasional breaking changes when using lower level parts of the API. Consider the plugin early access at this point, as there are lots of enhancements to add and polishing to do. A crucial part of that is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the third party services offer in a uniform way. That's why your feedback is much appreciated!

## Why?

* A centralized AI infrastructure **facilitates user choice**. Users may prefer certain AI services over other ones, and for many common tasks, either of the popular AI services is suitable. Having a common API regardless of the AI service allows leaving the choice to the user, rather than the plugin author.
* Since the centralized AI infrastructure comes with a common API that works the same for every AI service, it means **plugin developers don't have to spend as much time familiarizing themselves with different services**, at least when it comes to simple tasks. For tasks where certain services may have advantages over others, there is still flexibility to focus on a specific AI service.
* It also means **no more reinventing the wheel**: Since most AI services do not provide PHP SDKs for their APIs, many times this means WordPress plugins that want to leverage AI have to implement their own layer around the service's API. Not only is that time consuming, it also distracts from working on the actual (AI driven) features that the plugin should offer to its users. In fact this directly facilitates the user choice aspect mentioned, as having APIs for various AI services already provided means you can simply make those available to your plugin users.
* Having central AI infrastructure available **unlocks AI capabilities for smaller plugins or features**: It may not be worth the investment to implement a whole AI API layer for a simple AI driven feature, but when you already have it available, it can lead to more plugins (and thus more users) benefitting from AI capabilities.
* Last but not least, a central AI infrastructure means **users will only have to configure the AI API once**, e.g. paste their API keys only in a single WordPress administration screen. Without central AI infrastructure, every plugin has to provide its own UI for pasting API keys, making the process more tedious for site owners the more AI capabilities their site uses.

## Examples

**Generate the answer to a prompt in PHP code:**

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Helpers;

if ( ai_services()->has_available_services() ) {
	$service = ai_services()->get_available_service();
	try {
		$candidates = $service
			->get_model(
				array(
					'feature'      => 'my-test-feature',
					'capabilities' => array( AI_Capability::TEXT_GENERATION ),
				)
			)
			->generate_text( 'What can I do with WordPress?' );

		$text = Helpers::get_text_from_contents(
			Helpers::get_candidate_contents( $candidates )
		);

		echo $text;
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
```

**Generate the answer to a prompt in JavaScript code:**

```js
const helpers = aiServices.ai.helpers;
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices() ) {
	const service = getAvailableService();
	try {
		const candidates = await service.generateText(
			'What can I do with WordPress?',
			{ feature: 'my-test-feature' }
		);

		const text = helpers.getTextFromContents(
			helpers.getCandidateContents( candidates )
		);

		console.log( text );
	} catch ( error ) {
		// Handle the error.
	}
}
```

**Generate the answer to a prompt using WP-CLI:**

```bash
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature --user=admin
```

You can also use a specific AI service, if you have a preference, for example the `google` service.

**Generate the answer to a prompt using a specific AI service, in PHP code:**

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Helpers;

if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$candidates = $service
			->get_model(
				array(
					'feature'      => 'my-test-feature',
					'capabilities' => array( AI_Capability::TEXT_GENERATION ),
				)
			)
			->generate_text( 'What can I do with WordPress?' );

		$text = Helpers::get_text_from_contents(
			Helpers::get_candidate_contents( $candidates )
		);

		echo $text;
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
```

For complete examples such as entire plugins built on top of the AI Services infrastructure, please see the [examples directory on GitHub](https://github.com/felixarntz/ai-services/tree/main/examples).

Additionally, the [plugin documentation](./docs/README.md) provides granular examples including explainers.

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

Once the plugin is active, you will find a new _Settings > AI Services_ submenu in the WordPress administration menu. In there, you can configure your AI service API keys. After that, you can use the _Tools > AI Playground_ screen to explore the available AI capabilities of the different connected services.

If you have enabled the WordPress assistant chatbot via filter, you should see a small "Need help?" button in the lower right throughout WP Admin after you have configured at least one (valid) API key.

Please refer to the [plugin documentation](./docs/README.md) for instructions on how you can actually use the AI capabilities of the plugin in your own projects.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
