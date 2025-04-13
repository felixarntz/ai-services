=== AI Services ===

Plugin Name:  AI Services
Plugin URI:   https://felixarntz.github.io/ai-services/
Author:       Felix Arntz
Author URI:   https://felix-arntz.me
Contributors: flixos90
Tested up to: 6.8
Stable tag:   0.5.0
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Tags:         ai, text generation, image generation, function calling, multimodal

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

== Description ==

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for an AI Playground screen to explore AI capabilities as well as a settings screen to configure AI service credentials. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

Here's a (non-comprehensive) feature list:

* Abstraction layer and APIs to communicate with any AI service in a uniform way
  * APIs are available in both PHP and in JavaScript, as well as via the WordPress REST API and WP-CLI commands
  * Currently supports the following AI capabilities (with more on the way!):
    * text generation (including text streaming for more immediate feedback to users)
    * text chats with history
    * multimodal input
    * function calling
    * image generation
* AI Playground administration screen (in the Tools menu) allows exploring the different AI capabilities
  * Explore all AI capabilities supported by the plugin via user interface
  * Select which AI service and model to use and set a few advanced configuration parameters
  * Define your own function declarations used for AI function calling
  * Generate images and save them to the WordPress media library
  * Exchange the AI service or model on the fly to continue a chat started with one model with another one
* AI Services settings screen to configure services with API credentials
* Built-in AI service implementations
  * [Anthropic (Claude)](https://www.anthropic.com/claude)
  * [Google (Gemini, Imagen)](https://ai.google.dev/gemini-api)
  * [OpenAI (GPT, Dall-E)](https://openai.com/chatgpt/)
  * Browser (client-side only; experimental support for [Chrome's built-in AI APIs](https://developer.chrome.com/docs/ai/built-in-apis))
* Additional AI service integrations can be registered and will then be available in the same way as built-in ones

**Disclaimer:** The AI Services plugin is still in its early stages, with a limited feature set and more being added. A crucial part of refining the plugin is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the AI services offer in a uniform way. That's why your feedback is much appreciated!

= Why? =

* A centralized AI infrastructure **facilitates user choice**. Users may prefer certain AI services over other ones, and for many common tasks, either of the popular AI services is suitable. Having a common API regardless of the AI service allows leaving the choice to the user, rather than the plugin author.
* Since the centralized AI infrastructure comes with a common API that works the same for every AI service, it means **plugin developers don't have to spend as much time familiarizing themselves with different services**, at least when it comes to simple tasks. For tasks where certain services may have advantages over others, there is still flexibility to focus on a specific AI service.
* It also means **no more reinventing the wheel**: Since most AI services do not provide PHP SDKs for their APIs, many times this means WordPress plugins that want to leverage AI have to implement their own layer around the service's API. Not only is that time consuming, it also distracts from working on the actual (AI driven) features that the plugin should offer to its users. In fact this directly facilitates the user choice aspect mentioned, as having APIs for various AI services already provided means you can simply make those available to your plugin users.
* Having central AI infrastructure available **unlocks AI capabilities for smaller plugins or features**: It may not be worth the investment to implement a whole AI API layer for a simple AI driven feature, but when you already have it available, it can lead to more plugins (and thus more users) benefitting from AI capabilities.
* Last but not least, a central AI infrastructure means **users will only have to configure the AI API once**, e.g. paste their API keys only in a single WordPress administration screen. Without central AI infrastructure, every plugin has to provide its own UI for pasting API keys, making the process more tedious for site owners the more AI capabilities their site uses.

= Integration with third party services =

While the plugin APIs allow registering custom AI services, the plugin comes with a few popular AI services built-in. These AI services rely on the respective third party API. Their use is optional and it is up to you to choose which third party service you would like to use or whether you would like to use multiple.

The use of the third party AI services is subject to the respective terms of service. The following third party services are supported out of the box:

* [Anthropic (Claude)](https://www.anthropic.com/claude)
  * [Anthropic Consumer Terms of Service](https://www.anthropic.com/legal/consumer-terms)
  * [Anthropic Commercial Terms of Service](https://www.anthropic.com/legal/commercial-terms)
  * [Anthropic Privacy Policy](https://www.anthropic.com/legal/privacy)
* [Google (Gemini, Imagen)](https://ai.google.dev/gemini-api)
  * [Google Terms of Service](https://policies.google.com/terms)
  * [Google AI Terms of Service](https://policies.google.com/terms/generative-ai)
  * [Google Privacy Policy](https://policies.google.com/privacy)
* [OpenAI (GPT, Dall-E)](https://openai.com/chatgpt/)
  * [OpenAI Terms of Use](https://openai.com/policies/row-terms-of-use/)
  * [OpenAI Privacy Policy](https://openai.com/policies/row-privacy-policy/)

= Code examples for using the API =

**Generate the answer to a prompt in PHP code:**

`
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
`

**Generate the answer to a prompt in JavaScript code:**

`
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
`

**Generate the answer to a prompt using WP-CLI:**

`
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature
`

You can also use a specific AI service, if you have a preference, for example the `google` service.

**Generate the answer to a prompt using a specific AI service, in PHP code:**

`
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
`

Refer to the [plugin documentation](https://felixarntz.github.io/ai-services/Documentation.html) for granular examples including explainers.

For complete examples such as entire plugins built on top of the AI Services infrastructure, please see the [examples directory on GitHub](https://github.com/felixarntz/ai-services/tree/main/examples).

== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **AI Services**.
3. Install and activate the AI Services plugin.

= Manual installation =

1. Upload the entire `ai-services` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the AI Services plugin.

= Usage =

Once the plugin is active, you will find a new _Settings > AI Services_ submenu in the WordPress administration menu. In there, you can configure your AI service API keys. After that, you can use the _Tools > AI Playground_ screen to explore the available AI capabilities of the different connected services.

If you have enabled the WordPress assistant chatbot via filter, you should see a small "Need help?" button in the lower right throughout WP Admin after you have configured at least one (valid) API key.

Please refer to the [plugin documentation](https://felixarntz.github.io/ai-services/Documentation.html) for instructions on how you can actually use the AI capabilities of the plugin in your own projects.

== Frequently Asked Questions ==

= How can I customize AI Services model parameters? =

You can use the `ai_services_model_params` filter in PHP to customize the model parameters before they are used to retrieve a given AI service model.

This filter is run consistently in any context, regardless of whether the AI model is used via PHP, JavaScript, or WP-CLI.

This can be helpful, for example, if you need to inject custom model configuration parameters or a custom system instruction for a specific feature in a way that it happens dynamically on the server.

Here is an example code snippet which injects a custom system instruction whenever the feature `my-movie-expert` is used with any `google` model:

`
add_filter(
	'ai_services_model_params',
	function ( $params, $service ) {
		if ( 'my-movie-expert' === $params['feature'] && 'google' === $service ) {
			$params['systemInstruction']  = 'You are a movie expert. You can answer questions about movies, actors, directors, and movie references.';
			$params['systemInstruction'] .= ' If the user asks you about anything unrelated to movies, you should politely deny the request.';
			$params['systemInstruction'] .= ' You may use famous movie quotes in your responses to make the conversation more engaging.';
		}
		return $params;
	},
	10,
	2
);
`

Note that this filter does not allow you to change the `feature` parameter, as that needs to be controlled by the caller.

= How can I enable the WordPress Assistant chatbot feature?

There is a simple WordPress Assistant chatbot available as an experimental feature of the plugin, effectively acting as a proof of concept. Since the plugin is purely an infrastructure plugin that other plugins can use to access AI capabilities in WordPress, that chatbot feature is disabled by default.

If you want to test or use the chatbot, you can easily enable it via filter:

`
add_filter( 'ai_services_chatbot_enabled', '__return_true' );
`

= How can I tweak the WP-CLI commands' behavior? =

The `wp ai-services generate-text` command streams text responses by default. This can help provide more immediate feedback to the user, since chunks with partial response candidates will be available iteratively while the model still processes the remainder of the response.

An exception where it does not stream the response, but returns it all at once is if any function declarations are present.

If you prefer to show the complete text response in one go instead, you can disable streaming in WP-CLI by using the `ai_services_wp_cli_use_streaming` filter.

`
add_filter( 'ai_services_wp_cli_use_streaming', '__return_false' );
`

= How can I programmatically provide service API keys? =

If you prefer to not expose the sensitive controls over the AI service API keys to the site's end users, you can programmatically specify the keys by filtering the relevant service's option value.

For example, to enforce an API key to use for the Google AI service, you could use a code snippet like the following:

`
add_filter(
	'pre_option_ais_google_api_key',
	function () {
		return 'my-google-api-key';
	}
);
`

The same approach works for any other services too. Simply use the correct service slug, e.g. `openai` for the OpenAI integration and `anthropic` for the Anthropic integration.

= Which user capabilities are available and how can I customize them? =

[Please see the documentation article on customizing the available plugin capabilities.](https://felixarntz.github.io/ai-services/Customizing-the-Available-Capabilities.html)

= Should this be in WordPress Core? =

Probably not? At least not yet. While generative AI has been around for a few years, in the grand scheme of things we are still only scratching the surface of what's possible. But most importantly, the lack of standardization makes it difficult to consider built-in AI support in WordPress Core.

WordPress Core rarely adds support for features that rely on third party services. An exception is oEmbed support for many popular services, however via the common oEmbed endpoint that each service implements there is a standard way to have it work correctly without having to individually maintain each integration. Doing so would be a maintenance burden and it would make it almost impossible to stay on top of everything: Imagine one of the services makes a change - not only would this require to manually update the WordPress Core integration, but it would also require to quickly ship a new release ASAP because otherwise the WordPress sites using the service would break. Unfortunately, there is no such standard for how generative AI APIs provided by third party services should work. In other words, if you implement support for a generative AI API in your plugin, that implementation is subject to the same concern, and it applies to the AI Services plugin too. However, by centralizing the implementation in one plugin, the problem surface is greatly reduced. And differently from WordPress Core, it's more straightforward and more reasonable to ship a quick hotfix for this plugin.

The other reason that integrating generative AI in WordPress Core would be difficult is because (almost all) the services that make those APIs available require paid subscriptions. This is not well aligned with WordPress's FOSS philosophy. A potentially promising development that may change that situation is the introduction of browser built-in AI capabilities made available via JavaScript APIs, such as [Chrome built-in AI](https://developer.chrome.com/docs/ai) (which is also supported by the AI Services plugin).

Only time will tell whether those points can be addressed in a way that make built-in AI capabilities in WordPress Core a possibility. Until then, you can use a plugin like this one. While it is for obvious reasons not a WordPress Core feature plugin, it is in many ways built to potentially become a canonical AI plugin for WordPress:

* It is free, and always will be.
* It follows the WordPress Core philosophies.
* It uses WordPress UI components as much as possible.
* It is neutral and does not favor one AI service over another.

= Where should I submit my support request? =

For regular support requests, please use the [wordpress.org support forums](https://wordpress.org/support/plugin/ai-services). If you have a technical issue with the plugin where you already have more insight on how to fix it, you can also [open an issue on GitHub instead](https://github.com/felixarntz/ai-services/issues).

= How can I contribute to the plugin? =

If you have ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request in the [GitHub repository for the plugin](https://github.com/felixarntz/ai-services). Please stick to the [contributing guidelines](https://github.com/felixarntz/ai-services/blob/main/CONTRIBUTING.md).

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ai-services) to get started.

== Screenshots ==

1. The AI Services settings screen where users can paste their AI service credentials
2. Multimodal text generation in the AI Playground where users can explore the different AI model capabilities
3. Image generation in the AI Playground where users can explore the different AI model capabilities

== Changelog ==

= 0.5.0 =

**Features:**

* Introduce image generation support in PHP and JavaScript. ([6c3b328](https://github.com/felixarntz/ai-services/commit/6c3b32815766a0e8b203f9b6771e84bb0c7a0570))
* Introduce tool support and implement function calling in PHP and JavaScript. ([e84040c](https://github.com/felixarntz/ai-services/commit/e84040cce1679dac25de0b68e74df74d1c08ccb3))
* Introduce history persistence API in both PHP and JavaScript, to persistently save AI message histories in user meta. ([90e69b5](https://github.com/felixarntz/ai-services/commit/90e69b57ba4b6a76d107a3432a128d2e777b3dd7))
* Allow generating images in AI Playground. ([8b1902e](https://github.com/felixarntz/ai-services/commit/8b1902ebdbd89c3e595ef538ac44adbf2d1467d4))
* Allow uploading images and other media generated via AI Playground to the WordPress media library. ([a00af05](https://github.com/felixarntz/ai-services/commit/a00af05250cdc37ba131da50a3228a5e8e6d24ce))
* Allow creating and managing function declarations for AI function calling in AI Playground. ([abe6688](https://github.com/felixarntz/ai-services/commit/abe6688905f549af6f542f374435bcefb49dc28c))
* Allow sending function responses after receiving a function call in AI Playground. ([e16ab32](https://github.com/felixarntz/ai-services/commit/e16ab3283c73ceffbf74e425944f7c123666f9e0))
* Add WP-CLI command `wp ai-services generate-image` to generate images via the command line. ([599ce48](https://github.com/felixarntz/ai-services/commit/599ce4810fc770f47b85f9a3cd65f22e9f105bfc))
* Add support for passing multimodal prompt via attachment to WP-CLI command. ([4d2f320](https://github.com/felixarntz/ai-services/commit/4d2f3206c151d6fefcd5ad8af637051350e30ac2))
* Allow passing function declarations to WP-CLI `generate-text` command. ([f56b54b](https://github.com/felixarntz/ai-services/commit/f56b54b983bf696cc50c88b8c570fc4618a80e1a))

**Enhancements:**

* Implement image generation support for Google and OpenAI services. ([511571b](https://github.com/felixarntz/ai-services/commit/511571b690a105e0e35a358f0cc314e1e8d2c16a))
* Implement function calling tool support for Anthropic, Google, and OpenAI services. ([d47efef](https://github.com/felixarntz/ai-services/commit/d47efef13af4c2c0050aa58ef021c9187a1a14bf))
* Enhance AI Playground to save messages persistently using new history persistence API instead of session storage. ([919f26b](https://github.com/felixarntz/ai-services/commit/919f26b53db12d051380acc82e2db817084bb727))
* Enhance AI Playground service selection accessibility by announcing to screen readers when model selection was cleared. ([a3d7676](https://github.com/felixarntz/ai-services/commit/a3d767604f7b875191e0f2ada97eda54e49bdd1d))
* Enhance AI Playground chat messages accessibility by using log role. ([48a35c3](https://github.com/felixarntz/ai-services/commit/48a35c34220cf8e8be9737ff54ecb638725165fc))
* Enhance function declarations modal accessibility by using tab semantics for navigating through the function declarations. ([94e8494](https://github.com/felixarntz/ai-services/commit/94e8494b9c3ef6be2e66458ab3901dfc07a58d83))
* Show text instead of icon to clarify reset messages button purpose. ([843fef5](https://github.com/felixarntz/ai-services/commit/843fef520e2d2a9fc26e9fba37ca5f7c5c84fe6a))
* Centrally handle candidate count default of 1 to avoid wasted resources. ([568502a](https://github.com/felixarntz/ai-services/commit/568502a5379e876ca8208f916476a35505b7e50d))
* Implement several helper API methods related to dealing with binary files, blobs, and data URLs. ([a05d685](https://github.com/felixarntz/ai-services/commit/a05d685576fea47de9830cc0357d6578d62c06f0))
* Use new Anthropic API models endpoint to fetch available models remotely. ([a3fae90](https://github.com/felixarntz/ai-services/commit/a3fae900948958a9053b470ac991406f12109028))
* Show admin pointers to inform new users about where to configure AI Services and the ability to explore in the AI Playground. ([4d7a169](https://github.com/felixarntz/ai-services/commit/4d7a169491990c3d18c2f3f4bde647e37eb693c1))
* Simplify plugin settings link implementation by using abstraction from library. ([d77858a](https://github.com/felixarntz/ai-services/commit/d77858a9d169a7dd83cdd264502f5deeaf165636))
* Implement plugin action link and admin pointer for settings page. ([6566c77](https://github.com/felixarntz/ai-services/commit/6566c770652758b116c86d643c590b20494a43f7))
* Set default request timeout for image generation to 30 seconds. ([1cc086a](https://github.com/felixarntz/ai-services/commit/1cc086a4f136817e01b0184a30054114747ba489))
* Ensure that prompts with history are rejected if the model does not support chat history. ([bd01db0](https://github.com/felixarntz/ai-services/commit/bd01db04561f610d4bd9c74698846e1b5f7efe0a))
* Explicitly implement `With_Text_Generation` interface in text generation model classes. ([26985a3](https://github.com/felixarntz/ai-services/commit/26985a32f02cc7eb52f9514804e464e93879276f))
* Implement `Abstract_AI_Model` class and use it as foundation for service-specific model classes. ([3f998d9](https://github.com/felixarntz/ai-services/commit/3f998d9c67e40c3327edb40fb92e97bd8f831089))
* Deprecate service-specific `AI_Model` classes in favor of new AI_Text_Generation_Model classes. ([2f474aa](https://github.com/felixarntz/ai-services/commit/2f474aae98a626cad7cb8f94d320c5e8a924b263))
* Deprecate `Generation_Config` class in favor of new Text_Generation_Config class. ([d39c135](https://github.com/felixarntz/ai-services/commit/d39c13565ecadcacfd5b9d3e4b253d980790a3ce))
* Pass service slug to `ai_services_model_params` filter. Props [mslinnea](https://github.com/mslinnea). ([#23](https://github.com/felixarntz/ai-services/pull/23))
* Update Google service to prefer newer `gemini-2.0` models over older `gemini-1.5` models. ([b01dc0b](https://github.com/felixarntz/ai-services/commit/b01dc0b8f3dc746de8e3b43bbc88bba02b857040))
* Update OpenAI service to prefer newer `gpt-4o` models over more expensive `gpt-4` models and older `gpt-3.5` models. ([9b14367](https://github.com/felixarntz/ai-services/commit/9b14367fa90e7ef820f8e00d4130ae724884c44c))
* Update Anthropic service to prefer newer `claude-3.5` models over `claude-3` models. ([04f1896](https://github.com/felixarntz/ai-services/commit/04f1896f6f1a21e2b3590af4fb8996533b826d9c))
* Remove requirement of global `--user` argument for WP-CLI commands. Props [swissspidy](https://github.com/swissspidy). ([cd25081](https://github.com/felixarntz/ai-services/commit/cd250810fdd77201ee091fbb08268556f33d6496), [#25](https://github.com/felixarntz/ai-services/issues/25))
* Display model name above AI Playground responses if available. ([04c673a](https://github.com/felixarntz/ai-services/commit/04c673a2b7cac046539c42bdf596dc4af5bd6676))
* Implement new reusable components for common AI Playground use-cases and use `Flex` component in AI Playground where applicable. ([7c4f5d2](https://github.com/felixarntz/ai-services/commit/7c4f5d274643faa095a86d03e0151c1fd49a1fa0))
* Implement new helper functions to create a multimodal `Content` object from a prompt and media file, and to get the base64 data URL for a file. ([e664197](https://github.com/felixarntz/ai-services/commit/e664197e96fa6eb823dc8f56d882ede81a18d76a))
* Make `Parts` component publicly available via `aiServices.components`. ([3994156](https://github.com/felixarntz/ai-services/commit/39941566ee47f7a0089fe3018af8a264935d53ed))
* Expand available AI capabilities to include `FUNCTION_CALLING` and add support to applicable services and models. ([4dc570a](https://github.com/felixarntz/ai-services/commit/4dc570ad2b6c00aed2a4344d2c7b6900fb6b354b))
* Include name field in models data, retrieving it from the service API where available. ([40c4351](https://github.com/felixarntz/ai-services/commit/40c43510891d1c32cc8b07211c3b1e1081c32d3c))
* Alter return shape of `Generative_AI_Service::list_models()` method in PHP, for a consistent model data shape across PHP and JavaScript. ([a9193f9](https://github.com/felixarntz/ai-services/commit/a9193f98ee1ff46197530047f5a309dca595bb0a))
* Implement new helper function to get content object with text from a list of content objects. ([cf77062](https://github.com/felixarntz/ai-services/commit/cf77062343fef06bba280dbe2f24c0b465f06d20))

**Bug Fixes:**

* Fix bug where messages container in AI Playground could sometimes infinitely continue to scroll towards the bottom. ([f69fd30](https://github.com/felixarntz/ai-services/commit/f69fd309d752c3a1359bdd40f540d9a5daed3840))
* Fix AI capabilities missing from OpenAI o1 models. ([94086b4](https://github.com/felixarntz/ai-services/commit/94086b4da586fa3ee9684c9b19746e6423ebb926))
* Fix model response candidates to no longer include duplicate data that could inflate response size. ([3fc8422](https://github.com/felixarntz/ai-services/commit/3fc8422d7ab2f1df3ddf5f9c510c31c4f60d0203))
* Fix region navigation after `@wordpress/interface` package update. ([88d2c13](https://github.com/felixarntz/ai-services/commit/88d2c1340a243a39042c2babda62c13f2b02171a))
* Fix limited data URL regex to support MIME types that contain numbers or hyphens. ([c00e8be](https://github.com/felixarntz/ai-services/commit/c00e8bee4669fb5fe780fb950b8332a87db533d0))
* Fix incorrect model capabilities being indicated for legacy Anthropic and legacy Google AI models. ([4cbabab](https://github.com/felixarntz/ai-services/commit/4cbababbb89786ae6e5eb1a450d1bbf40926746a))
* Fix API request options not being passed in `Generative_AI_Service::list_models()` implementations. ([f50e46f](https://github.com/felixarntz/ai-services/commit/f50e46facbd67f8b79bdc49eeed19a88fc44605b))
* Fix bug in `Service_Entity_Query::count()` method. ([08cb9ba](https://github.com/felixarntz/ai-services/commit/08cb9ba6e4657310ab97a327b16dd989cb655ffc))

**Documentation:**

* Expand PHP, JS, and WP-CLI documentation to cover how to generate images. ([34689f9](https://github.com/felixarntz/ai-services/commit/34689f979b64374cdf97fb420030f4dfe9721524))
* Include documentation on how to use function calling in both PHP and JavaScript. ([4ded68f](https://github.com/felixarntz/ai-services/commit/4ded68f2d4e92cd067978ee4c41f2fff93610224))
* Include intro section in JavaScript API documentation on how to enqueue the API. ([366d7ea](https://github.com/felixarntz/ai-services/commit/366d7ea2579fe42da7abe16d03d737e9153b250a))
* Expand WP-CLI command documentation to cover how to send multimodal prompts and handle function calling. ([b7dd38b](https://github.com/felixarntz/ai-services/commit/b7dd38b2c07e93758e9e013905bb79a37134a9b4))

= 0.4.0 =

**Features:**

* Add AI Playground screen which allows to explore services and models with their configurations and behaviors. ([1495994](https://github.com/felixarntz/ai-services/commit/1495994399a39baf300b4f70d3f8c93ebf29059e))
* Add REST route and expand `ai-services/ai` store to provide general plugin data and user capabilities for JavaScript consumption. ([83d770c](https://github.com/felixarntz/ai-services/commit/83d770cd31eb598bd8b1a52e5f117655ef8a1075))

**Enhancements:**

* Ensure AI playground message data can be stored in session storage by avoiding to include inline data for attachments. ([b667611](https://github.com/felixarntz/ai-services/commit/b667611c539529ac2f34fcb933af9675dc75d41a), [7d346a2](https://github.com/felixarntz/ai-services/commit/7d346a2423bad8d1bae84c82abb80899e565066d))
* Implement store infrastructure to manage panel state and persist open/closed AI playground panels. ([89572e8](https://github.com/felixarntz/ai-services/commit/89572e8e2368d68894b82287e639b483d0840f6f))
* Implement AI playground panel to allow customizing most commonly supported AI model config parameters. ([27ea03f](https://github.com/felixarntz/ai-services/commit/27ea03f54f14bb840efb635673f5f3cad83a3508))
* Support providing message history alongside new prompt in AI playground. ([33c54cc](https://github.com/felixarntz/ai-services/commit/33c54cc202b1466b726e6fedf645682896fd2dba))
* Allow to reset the list of messages in the AI playground. ([9537986](https://github.com/felixarntz/ai-services/commit/95379864c53aef24a3c9f453b89da200eb9e4ae7))
* Persist messages from AI playground in session storage. ([60c45d9](https://github.com/felixarntz/ai-services/commit/60c45d99e82807f9d8bddc386e71a98d3c9fe7b3))
* Expand interface package with store for easier abstraction and a new `Modal` component. ([5ee0e20](https://github.com/felixarntz/ai-services/commit/5ee0e20ba09194774ca7d6902607500fd6a2ace6))
* Implement playground UI to select an attachment from the media library to provide as multimodal input. ([dbad8d3](https://github.com/felixarntz/ai-services/commit/dbad8d360a64649b00e1fa033e2f291a6a59568b))
* Enhance playground input by allowing arrow navigation to access previous messages and automatically focusing on it. ([f44efa5](https://github.com/felixarntz/ai-services/commit/f44efa501bde7a0ef8ad104b4c7ae54fe523d148))
* Implement keyboard shortcut to toggle system instruction. ([66f990d](https://github.com/felixarntz/ai-services/commit/66f990da149a35b3f90432f4bacbf21081b45828))
* Automatically scroll to latest messages as new messages arrive. ([c8f3746](https://github.com/felixarntz/ai-services/commit/c8f37468e96d8ed6778f7f41ec1fe18684e536b0))
* Implement `getServiceName` and `getServiceCredentialsUrl` selectors in `ai-services/ai` store for parity with PHP API. ([d9ca118](https://github.com/felixarntz/ai-services/commit/d9ca1184a4479beca255b3a40a5a697874c07acd))
* Implement store `ai-services/ai-playground` for new AI playground screen. ([96f5b45](https://github.com/felixarntz/ai-services/commit/96f5b454ecb79969523f70febd22d588a00f98ee))
* Use newer `ai.languageModel` property for Chrome built-in AI, continuing to support `ai.assistant` for backward compatibility. ([0f52227](https://github.com/felixarntz/ai-services/commit/0f5222725419881edcea91fb4524248f005623fb))
* Remove unused option. ([28a864a](https://github.com/felixarntz/ai-services/commit/28a864a24e70ff31a5577e69c6025fca94331b5e))
* Ensure service options are set to (temporarily) not autoload when plugin gets deactivated. ([4841d5b](https://github.com/felixarntz/ai-services/commit/4841d5b5670131b14ceb908a1ee05d056dab3fd4))

**Bug Fixes:**

* Fix AI playground automatic message scrolling to correctly function with multiple quick subsequent updates. ([50f40fe](https://github.com/felixarntz/ai-services/commit/50f40fe649a444af0a7efaaa2539fef1361b2b7c))
* Fix sidebar toggle button being hidden on mobile. ([9d44650](https://github.com/felixarntz/ai-services/commit/9d4465058050effeb256aad1bb12698752500275))
* Fix bugs with sidebar handling and support keyboard shortcut in interface abstraction. ([cf7b926](https://github.com/felixarntz/ai-services/commit/cf7b926f9810dd664e8a8b016bfa6b8a0835455f))

**Documentation:**

* Add documentation about the available user capabilities and how to customize them. ([a1d972b](https://github.com/felixarntz/ai-services/commit/a1d972bdca6121cd3358827798f2c6cd0a2f79ea))
* Expand readme and documentation to reference the new AI Playground screen. ([a136c1c](https://github.com/felixarntz/ai-services/commit/a136c1c0a7f1685593ec13c8084f436c665f5a27))

= 0.3.0 =

**Features:**

* Add text streaming support to generative models in JavaScript. ([9967db5](https://github.com/felixarntz/ai-services/commit/9967db5ac2c7d659345a01bb6acc52978c9278ef), [#3](https://github.com/felixarntz/ai-services/issues/3))
* Introduce REST route to stream generate text, using an event stream. ([071a664](https://github.com/felixarntz/ai-services/commit/071a664e7b0693870b3f0b822451410bdc5e1875), [#3](https://github.com/felixarntz/ai-services/issues/3))
* Add text streaming support to all built-in services Anthropic, Google, OpenAI. ([e27697a](https://github.com/felixarntz/ai-services/commit/e27697a581204652c8eb08fb37775e185cbb376a), [#3](https://github.com/felixarntz/ai-services/issues/3))
* Introduce API foundation for streaming text responses. ([9476333](https://github.com/felixarntz/ai-services/commit/9476333b0b4e0a968eef6a84743924caaa2be844))

**Enhancements:**

* Polish and complete implementation of Chrome browser built-in AI integration. ([1beb2c5](https://github.com/felixarntz/ai-services/commit/1beb2c5748821ccd2efed31592f99dd03d41423a), [#6](https://github.com/felixarntz/ai-services/issues/6))
* Support text streaming in chat implementations in both PHP and JavaScript. ([9e11c03](https://github.com/felixarntz/ai-services/commit/9e11c034b2f5906b54f33e40a3b0645cc199379f), [#3](https://github.com/felixarntz/ai-services/issues/3))
* Use streaming by default for the built-in chatbot. ([3f6266b](https://github.com/felixarntz/ai-services/commit/3f6266b81893b2abcaa8774eb513c3ca3845b1f7))
* Use streaming by default for WP-CLI text generation, customizable via filter. ([f9be4ad](https://github.com/felixarntz/ai-services/commit/f9be4ad955400aa825235f86868445067d2e831e))
* Remove unnecessary `console.log` call for chatbot. ([7637632](https://github.com/felixarntz/ai-services/commit/7637632e92c04338b096f7ec7696a93d80693623))
* Persist chatbot messages history in session storage. ([a349274](https://github.com/felixarntz/ai-services/commit/a349274a4b072a1676410151079dd49f224c9b5b), [#4](https://github.com/felixarntz/ai-services/issues/4))
* Persist chatbot visibility across page loads. ([81a0511](https://github.com/felixarntz/ai-services/commit/81a051151aed336312b59dcca5205b77fa372cd6), [#4](https://github.com/felixarntz/ai-services/issues/4))
* Include chatbot input label for screen reader users for better accessibility. ([94026e5](https://github.com/felixarntz/ai-services/commit/94026e5fed3f14977ba4ad57321755e450816a8c), [#4](https://github.com/felixarntz/ai-services/issues/4))
* Improve chatbot accessibility by focusing on input when the chatbot is opened. ([7b5c6f4](https://github.com/felixarntz/ai-services/commit/7b5c6f4eb7b42beb823dcbc268245df887cbbf2d), [#4](https://github.com/felixarntz/ai-services/issues/4))
* Improve chatbot error handling by displaying technical errors as a chatbot response. ([e57d716](https://github.com/felixarntz/ai-services/commit/e57d7165c255536015ffadb136b6181900b816b0))
* Show loading ellipsis in chatbot while generating text response. ([db79515](https://github.com/felixarntz/ai-services/commit/db7951596fd95e9f5c0b448f2f39b87336b96413), [#4](https://github.com/felixarntz/ai-services/issues/4))
* Handle errors during browser AI session creation more gracefully. ([0edd56f](https://github.com/felixarntz/ai-services/commit/0edd56f350deced5dde25bab84c5377f42365add))
* Consistently handle AI temperature parameter between services, expecting a value between 0.0 and 1.0. ([e7ae611](https://github.com/felixarntz/ai-services/commit/e7ae611b34edbf82ec6cd9fa9ee97cfa7d4423a6))
* Improve error handling in chat store and built-in chatbot. ([06b2340](https://github.com/felixarntz/ai-services/commit/06b23400c2c8837977a8ba3a43d4cdee44d81789))
* Expand AI capabilities with `CHAT_HISTORY` capability to differentiate between whether text generation models support history. ([4c2feb4](https://github.com/felixarntz/ai-services/commit/4c2feb4112f9dda700a34f425eb3eab0831bee13))
* Provide helper function in PHP and JS to aggregate chunks from candidates stream into final candidates response. ([a27bdf6](https://github.com/felixarntz/ai-services/commit/a27bdf65042200d9f55ee3a4c83bb56d0d31032f), [#3](https://github.com/felixarntz/ai-services/issues/3))
* Ensure third-party production libraries are always backward compatible with minimum supported PHP version by separating tooling. ([e4fe291](https://github.com/felixarntz/ai-services/commit/e4fe2919da046e742dc2c40d4b1a5e90e3e480bd))
* Enhance JavaScript API with model instances for better parity with PHP API, while continuing to allow previous approach as short-hand syntax. ([7992d6d](https://github.com/felixarntz/ai-services/commit/7992d6dd5adb72ea57857f5f5c548563f0e30000))
* Restructure JavaScript code into separate files per class. ([cd8d90d](https://github.com/felixarntz/ai-services/commit/cd8d90da9bfa59fcb31174279ef41c36fde60e55))
* Remove specific API client interface methods that should not be required for the interface. ([140d7c1](https://github.com/felixarntz/ai-services/commit/140d7c128875b5a73c5a31a93e4f1793ea3599d0))
* Allow candidates to have no content. ([c072689](https://github.com/felixarntz/ai-services/commit/c072689ebff295ff40de1e8bd57e3abc28b35c14))

**Bug Fixes:**

* Remove prefix from base64 inline image data for Anthropic AI integration. Props [mslinnea](https://github.com/mslinnea). ([#19](https://github.com/felixarntz/ai-services/pull/19))
* Fix bug in `CandidatesStreamProcessor` in JS, leading to stream responses to not being aggregated correctly. ([969b554](https://github.com/felixarntz/ai-services/commit/969b55414945dc598528f894f741ae92b151adea))
* Fix OpenAI model definitions by restricting to `gpt-4o` models for multimodal support. Props [mslinnea](https://github.com/mslinnea). ([#18](https://github.com/felixarntz/ai-services/pull/18))
* Split components package into distinct components and interface packages to better separate responsibilities and avoid JS warning outside of AI Services admin screen. Props [westonruter](https://github.com/westonruter). ([056461c](https://github.com/felixarntz/ai-services/commit/056461c92014926777172424073eb70aac172b5d), [#13](https://github.com/felixarntz/ai-services/issues/13))
* Fix chatbot bug where unexpected AI response could lead to link button to contain unexpected label and overflow its container. ([66f3578](https://github.com/felixarntz/ai-services/commit/66f357883371d6d14a3805615ace681a411d4741))
* Fix UI warnings in WordPress 6.7 due to JS component updates. ([6e8d231](https://github.com/felixarntz/ai-services/commit/6e8d2317d2db9d049efd3ce9e63c128bc7fe72a3))
* Fix failing Anthropic API requests when no generation config was provided. ([33db20b](https://github.com/felixarntz/ai-services/commit/33db20be1e4179d0776401090f363456994c98a3))

**Documentation:**

* Include documentation section about using browser built-in AI in JavaScript. ([91c5be7](https://github.com/felixarntz/ai-services/commit/91c5be76a46091d235d7eeeb78f44ae0178a9c1d), [#6](https://github.com/felixarntz/ai-services/issues/6))
* Expand documentation to explain how to customize model configuration. ([930058a](https://github.com/felixarntz/ai-services/commit/930058ab3e8ffbbb6adadbbb4022887b6be17e89))
* Expand documentation to cover how to use new text streaming capabilities in PHP and JS. ([20d028b](https://github.com/felixarntz/ai-services/commit/20d028ba3cade8d1c98a7694ce51816515d7cc84), [#3](https://github.com/felixarntz/ai-services/issues/3))

= 0.2.0 =

**Features:**

* Introduce `ai_services_model_params` filter to centrally customize AI service model parameters. ([f36f35d](https://github.com/felixarntz/ai-services/commit/f36f35d5a37b52375969b2e19d6364b5ff540072))
* Add enums to the public APIs in PHP and JavaScript, for now covering AI capabilities and content roles. ([48dedc5](https://github.com/felixarntz/ai-services/commit/48dedc50f9a86e38bb0f1ac7dbbb0afd7beaea4b))
* Add WP-CLI support under `ai-services` namespace with commands `list`, `get`, `list-models`, and `generate-text`. ([415edbc](https://github.com/felixarntz/ai-services/commit/415edbc0aa720c560fbef348563ebfb9c2fb494c), [#7](https://github.com/felixarntz/ai-services/issues/7))
* Introduce helpers as object with useful functions in both PHP and JavaScript APIs. ([98ae179](https://github.com/felixarntz/ai-services/commit/98ae1797746c78f548d5f1c385a2d9ac9fbd72c4), [7cf8a4d](https://github.com/felixarntz/ai-services/commit/7cf8a4daff620877460b0a45f106006911c8866b))
* Introduce `Generation_Config` type class for safer and more consistent handling of model generation config data. ([4e6925a](https://github.com/felixarntz/ai-services/commit/4e6925ab324089ad421a03af89191b6cf44094e1))

**Enhancements:**

* Add Settings link to plugin row actions. Props [westonruter](https://github.com/westonruter). ([#12](https://github.com/felixarntz/ai-services/pull/12))
* Remove unnecessary `With_API_Client` interface and related method. ([f3dc6b4](https://github.com/felixarntz/ai-services/commit/f3dc6b42dc62f31e1d803772258f9246a59b3354))
* Move `Felix_Arntz\AI_Services\Services\Types` namespace to `Felix_Arntz\AI_Services\Services\API\Types` to indicate it is part of the public API. ([5e34f7a](https://github.com/felixarntz/ai-services/commit/5e34f7a26e0b6b3f7b4f953b0765bbd1084d0763))
* Enhance content part classes by providing dedicated getter functions. ([89ae723](https://github.com/felixarntz/ai-services/commit/89ae723eca7fc6ff236ca9cc1402f463c527baf3))
* Move internal `Service_Entity` and `Service_Entity_Query` classes to their own namespace, since they are not only relevant for the REST API. ([4ce7026](https://github.com/felixarntz/ai-services/commit/4ce7026e4913870a63a0d13b3592003081471c36))
* Change built-in assistant chatbot feature to be opt-in rather than opt-out. ([9279850](https://github.com/felixarntz/ai-services/commit/92798508d5c566f0596a46ee665e3b664649dc16), [#15](https://github.com/felixarntz/ai-services/issues/15))
* Rename `ai-store` asset to `ai` and `settings-store` asset to `settings` and adjust JS globals accordingly, keeping old `ai-store` asset and JS global available for backward compatibility. ([fbe4916](https://github.com/felixarntz/ai-services/commit/fbe49168576230b11e2c02f107da4193a888cb7a))
* Strengthen prompt content validation and add support for OpenAI audio input. ([350c85d](https://github.com/felixarntz/ai-services/commit/350c85ddde3c1403e746cf801a6325fdfde4be1e))
* Allow passing through arbitrary parameters to built-in service APIs. ([81254f6](https://github.com/felixarntz/ai-services/commit/81254f62795695114da3aefbf0789c5637bb7aec))
* Enhance generation config transformation to support equivalent arguments across the built-in service APIs for Anthropic, Google, and OpenAI. ([69a99bf](https://github.com/felixarntz/ai-services/commit/69a99bf708a39083a2e4122d877be580cd90ec08))
* Validate feature model param in REST API and mark relevant parameters as required. ([2032690](https://github.com/felixarntz/ai-services/commit/2032690a5e825e836e46bb3dc06a38f245172ea9))
* Enhance chatbot to rely on feature identifier instead of custom property to inject model params. ([962750b](https://github.com/felixarntz/ai-services/commit/962750b126a3e97a498d5fd4a689303e1470b054))
* Consistently handle the Google-specific `safetySettings` model parameter, expecting an array of `Safety_Setting` instances. ([a74e51c](https://github.com/felixarntz/ai-services/commit/a74e51c70ead3fdd474861b8748c28163e403dd6))
* Allow passing system instruction as data array to REST endpoint. ([7b4916a](https://github.com/felixarntz/ai-services/commit/7b4916a5125363ca3578b3f52a728279a96740fc))
* Use camelCase arguments for model params for more consistency with underlying APIs. ([946c448](https://github.com/felixarntz/ai-services/commit/946c4489863a4c88887b6fb74a32f7a47136a4fc))

**Bug Fixes:**

* Fix conflict between REST content schemas. Props [westonruter](https://github.com/westonruter). ([e087602](https://github.com/felixarntz/ai-services/commit/e087602f8e4a75f2cfe14af22d69d49ce245ebac), [#14](https://github.com/felixarntz/ai-services/issues/14))
* Fix early component return in example plugin. ([e7ce054](https://github.com/felixarntz/ai-services/commit/e7ce0543d899522c79525123ca448b6d7260d6a0))

**Documentation:**

* Update documentation to cover WP-CLI usage and latest API enhancements. ([21ab225](https://github.com/felixarntz/ai-services/commit/21ab225d68e1f00ed909556b3920a727cd33af6b))
* Improve documentation to cover how to process generative model responses. ([c40c89d](https://github.com/felixarntz/ai-services/commit/c40c89d9154bcb126867b4640c363e20c2ddbdac))

= 0.1.1 =

**Bug Fixes:**

* Update Prompt API to latest shape. Props [tomayac](https://github.com/tomayac). ([#11](https://github.com/felixarntz/ai-services/pull/11))
* Fix bug preventing inline data to be processed by Google AI API. ([cf57baf](https://github.com/felixarntz/ai-services/commit/cf57baf8822a5c2a9a13760c4d7fa6a6def45558))
* Fix OpenAI model configuration to only provide multimodal capabilities for GPT-4 models. ([42ba79b](https://github.com/felixarntz/ai-services/commit/42ba79bf45b063279fc714fade604b6a3aafb894))
* Fix bug where REST endpoint to generate content did not accept content in its complex shape. ([2e0687f](https://github.com/felixarntz/ai-services/commit/2e0687f5620e6a2d4a4ea28527eef64d2f32adb1))

= 0.1.0 =

* Initial early access release. [See announcement post.](https://felix-arntz.me/blog/introducing-the-ai-services-plugin-for-wordpress/)
