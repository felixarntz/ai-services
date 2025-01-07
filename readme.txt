=== AI Services ===

Plugin Name:  AI Services
Plugin URI:   https://wordpress.org/plugins/ai-services/
Author:       Felix Arntz
Author URI:   https://felix-arntz.me
Tested up to: 6.7
Stable tag:   0.3.0
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

== Description ==

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for an AI Playground screen to explore AI capabilities as well as a settings screen to configure AI service credentials. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

Here's a (non-comprehensive) feature list:

* Abstraction layer and APIs to communicate with any AI service in a uniform way
  * APIs are available in both PHP and in JavaScript, as well as via WP-CLI commands
  * Supports streaming text generation for more immediate feedback to users
  * Currently only supports text generation (including multi-modal support if supported by the AI service), but support for additional capabilities (e.g. image generation, audio generation) will be added soon
* Built-in AI service implementations
  * [Anthropic (Claude)](https://www.anthropic.com/claude)
  * [Google (Gemini)](https://ai.google.dev/gemini-api)
  * [OpenAI (ChatGPT)](https://openai.com/chatgpt/)
  * Browser (client-side only; experimental support for [Chrome's built-in AI APIs](https://developer.chrome.com/docs/ai/built-in-apis))
* Additional AI service integrations can be registered and will then be available in the same way as built-in ones
* AI Playground administration screen (in the Tools menu) allows exploring the different AI capabilities
* AI Services settings screen to configure services with API credentials

**Disclaimer:** The AI Services plugin is still in its early stages, with a limited feature set. As long as it is in a `0.x.y` version, there may be occasional breaking changes when using lower level parts of the API. Consider the plugin early access at this point, as there are lots of enhancements to add and polishing to do. A crucial part of that is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the third party services offer in a uniform way. That's why your feedback is much appreciated!

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
* [Google (Gemini)](https://ai.google.dev/gemini-api)
  * [Google Terms of Service](https://policies.google.com/terms)
  * [Google AI Terms of Service](https://policies.google.com/terms/generative-ai)
  * [Google Privacy Policy](https://policies.google.com/privacy)
* [OpenAI (ChatGPT)](https://openai.com/chatgpt/)
  * [OpenAI Terms of Use](https://openai.com/policies/row-terms-of-use/)
  * [OpenAI Privacy Policy](https://openai.com/policies/row-privacy-policy/)

= Examples =

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
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature --user=admin
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

For complete examples such as entire plugins built on top of the AI Services infrastructure, please see the [examples directory on GitHub](https://github.com/felixarntz/ai-services/tree/main/examples).

Additionally, the [plugin documentation](https://github.com/felixarntz/ai-services/tree/main/docs/README.md) provides granular examples including explainers.

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

Please refer to the [plugin documentation](https://github.com/felixarntz/ai-services/tree/main/docs/README.md) for instructions on how you can actually use the AI capabilities of the plugin in your own projects.

== Frequently Asked Questions ==

= How can I customize AI Services model parameters? =

You can use the `ai_services_model_params` filter in PHP to customize the model parameters before they are used to retrieve a given AI service model.

This filter is run consistently in any context, regardless of whether the AI model is used via PHP, JavaScript, or WP-CLI.

This can be helpful, for example, if you need to inject custom model configuration parameters or a custom system instruction for a specific feature in a way that it happens dynamically on the server.

Here is an example code snippet which injects a custom system instruction whenever the feature `my-movie-expert` is used:

`
add_filter(
	'ai_services_model_params',
	function ( $params ) {
		if ( 'my-movie-expert' === $params['feature'] ) {
			$params['systemInstruction']  = 'You are a movie expert. You can answer questions about movies, actors, directors, and movie references.';
			$params['systemInstruction'] .= ' If the user asks you about anything unrelated to movies, you should politely deny the request.';
			$params['systemInstruction'] .= ' You may use famous movie quotes in your responses to make the conversation more engaging.';
		}
		return $params;
	}
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

The `wp ai-services generate-text` command streams text responses by default, providing faster feedback to the user. If you prefer to show the complete text response in one go instead, you can disable streaming in WP-CLI by using the `ai_services_wp_cli_use_streaming` filter.

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

[Please see the documentation article on customizing the available plugin capabilities.](https://github.com/felixarntz/ai-services/blob/main/docs/Customizing-the-Available-Capabilities.md)

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
2. The AI Playground where users can explore the AI capabilities of the different services

== Changelog ==

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
