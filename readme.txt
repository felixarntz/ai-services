=== AI Services ===

Plugin Name:  AI Services
Plugin URI:   https://wordpress.org/plugins/ai-services/
Author:       Felix Arntz
Author URI:   https://felix-arntz.me
Tested up to: 6.6
Stable tag:   0.1.1
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

== Description ==

**Disclaimer:** The AI Services plugin is still in its very early stages, with a limited feature set. As long as it is in a `0.x.y` version, expect occasional breaking changes. Consider the plugin early access at this point, as there are lots of enhancements to add and polishing to do. A crucial part of that is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the third party services offer in a uniform way. That's why your feedback is much appreciated!

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for a simple WordPress support assistant chatbot that can be disabled if not needed. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

Here's a (non-comprehensive) feature list:

* Abstraction layer and APIs to communicate with any AI service in a uniform way
  * APIs are available in both PHP and in JavaScript, and WP-CLI commands are being worked on
  * Currently only supports text generation (including multi-modal support if supported by the AI service), but support for additional capabilities (e.g. image generation, audio generation) will be added soon
* Built-in AI service implementations
  * [Anthropic (Claude)](https://www.anthropic.com/claude)
  * [Google (Gemini)](https://ai.google.dev/gemini-api)
  * [OpenAI (ChatGPT)](https://openai.com/chatgpt/)
  * Browser (client-side only; experimental support for [Chrome's built-in AI APIs](https://developer.chrome.com/docs/ai/built-in-apis))
* Additional AI service integrations can be registered and will then be available in the same way as built-in ones
* WordPress Assistant chatbot is the single user-facing built-in feature the plugin comes with
  * This effectively is a simple proof of concept of how the APIs the plugin provides can be used
  * The chatbot feature is inactive by default and can easily be [enabled via filter](#how%20can%20i%20enable%20the%20wordpress%20assistant%20chatbot%20feature%3F)
  * No other user-facing features will ever be added - that's a promise - because this is first and foremost an **infrastructure plugin** that other plugins can rely on

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
if ( ai_services()->has_available_services() ) {
	$service = ai_services()->get_available_service();
	try {
		$candidates = $service
			->get_model(
				array(
					'feature'      => 'my-test-feature',
					'capabilities' => array( 'text_generation' ),
				)
			)
			->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
`

**Generate the answer to a prompt in JavaScript code:**

`
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices() ) {
	const service = getAvailableService();
	try {
		const candidates = await service.generateText(
			'What can I do with WordPress?',
			{ feature: 'my-test-feature' }
		);
	} catch ( error ) {
		// Handle the error.
	}
}
`

**Generate the answer to a prompt using WP-CLI:**

`
wp ai-services generate-text 'What can I do with WordPress?' --feature=my-test-feature
`

You can also use a specific AI service, if you have a preference, for example the `google` service.

**Generate the answer to a prompt using a specific AI service, in PHP code:**

`
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$candidates = $service
			->get_model(
				array(
					'feature'      => 'my-test-feature',
					'capabilities' => array( 'text_generation' ),
				)
			)
			->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
`

**Generate the answer to a prompt using a specific AI service, using the REST API via cURL:**

`
curl 'https://example.com/wp-json/ai-services/v1/services/google:generate-text' \
  -H 'Content-Type: application/json' \
  --data-raw '{"content":"What can I do with WordPress?"}'
`

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

You can configure the plugin with your AI service credentials using the _Settings > AI Services_ screen in the WP Admin menu.

== Frequently Asked Questions ==

= How can I enable the WordPress Assistant chatbot feature?

There is a single user-facing built-in feature the plugin comes with, which is a simple WordPress Assistant chatbot, effectively acting as a proof of concept. Since the plugin is purely an infrastructure plugin that other plugins can use to access AI capabilities in WordPress, that chatbot feature is disabled by default.

If you want to test or use the chatbot, you can easily enable it via filter:

`
add_filter( 'ai_services_chatbot_enabled', '__return_true' );
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

1. The AI Services settings screen where end users can paste their AI service credentials

== Changelog ==

= 0.1.1 =

**Bug Fixes:**

* Update Prompt API to latest shape. Props [tomayac](https://github.com/tomayac). See [#11](https://github.com/felixarntz/ai-services/pull/11).
* Fix bug preventing inline data to be processed by Google AI API. See [cf57baf](https://github.com/felixarntz/ai-services/commit/cf57baf8822a5c2a9a13760c4d7fa6a6def45558).
* Fix OpenAI model configuration to only provide multimodal capabilities for GPT-4 models. See [42ba79b](https://github.com/felixarntz/ai-services/commit/42ba79bf45b063279fc714fade604b6a3aafb894).
* Fix bug where REST endpoint to generate content did not accept content in its complex shape. See [2e0687f](https://github.com/felixarntz/ai-services/commit/2e0687f5620e6a2d4a4ea28527eef64d2f32adb1).

= 0.1.0 =

* Initial early access release. [See announcement post.](https://felix-arntz.me/blog/introducing-the-ai-services-plugin-for-wordpress/)
