=== AI Services ===

Plugin Name:  AI Services
Plugin URI:   https://felixarntz.github.io/ai-services/
Author:       Felix Arntz
Author URI:   https://felix-arntz.me
Contributors: flixos90
Tested up to: 6.9
Stable tag:   0.7.1
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
    * [Mistral](https://mistral.ai/)
    * [OpenAI (GPT, Dall-E)](https://openai.com/chatgpt/)
    * [Perplexity (Sonar)](https://www.perplexity.ai/)
    * [xAI (Grok)](https://x.ai/)
    * Browser (client-side only; experimental support for [Chrome's built-in AI APIs](https://developer.chrome.com/docs/ai/built-in-apis) and [Edge's built-in AI APIs](https://blogs.windows.com/msedgedev/2025/05/19/introducing-the-prompt-and-writing-assistance-apis/))
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
* [Mistral](https://mistral.ai/)
    * [Mistral Terms of Service](https://mistral.ai/terms#terms-of-service)
    * [Mistral Privacy Policy](https://mistral.ai/terms#privacy-policy)
* [OpenAI (GPT, Dall-E)](https://openai.com/chatgpt/)
    * [OpenAI Terms of Use](https://openai.com/policies/row-terms-of-use/)
    * [OpenAI Privacy Policy](https://openai.com/policies/row-privacy-policy/)
* [Perplexity (Sonar)](https://www.perplexity.ai/)
    * [Perplexity Terms of Service](https://www.perplexity.ai/hub/legal/terms-of-service)
    * [Perplexity Privacy Policy](https://www.perplexity.ai/hub/legal/privacy-policy)
* [xAI (Grok)](https://x.ai/)
    * [xAI Terms of Service - Consumer](https://x.ai/legal/terms-of-service)
    * [xAI Terms of Service - Enterprise](https://x.ai/legal/terms-of-service-enterprise)
    * [xAI Privacy Policy](https://x.ai/legal/privacy-policy)

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

Most certainly at some point, in some form. But maybe not yet. While generative AI has been around for a few years, in the grand scheme of things we are still only scratching the surface of what's possible. But most importantly, the lack of standardization makes it difficult to consider built-in AI support in WordPress Core.

WordPress Core rarely adds support for features that rely on third party services. An exception is oEmbed support for many popular services, however via the common oEmbed endpoint that each service implements there is a standard way to have it work correctly without having to individually maintain each integration. Doing so would be a maintenance burden and it would make it almost impossible to stay on top of everything: Imagine one of the services makes a change - not only would this require to manually update the WordPress Core integration, but it would also require to quickly ship a new release ASAP because otherwise the WordPress sites using the service would break. Unfortunately, there is no such standard for how generative AI APIs provided by third party services should work. In other words, if you implement support for a generative AI API in your plugin, that implementation is subject to the same concern, and it applies to the AI Services plugin too. However, by centralizing the implementation in one plugin, the problem surface is greatly reduced. And differently from WordPress Core, it's more straightforward and more reasonable to ship a quick hotfix for this plugin.

The other reason that integrating generative AI in WordPress Core would be difficult is because (almost all) the services that make those APIs available require paid subscriptions. This is not well aligned with WordPress's FOSS philosophy. A promising development that may change that situation is the increasing availability of on-device models including browser built-in AI models, such as [Chrome built-in AI](https://developer.chrome.com/docs/ai) (which is also supported by the AI Services plugin).

Until the time is right for built-in AI capabilities in WordPress Core, you can use a plugin like this one. While it is for now not a WordPress Core feature plugin, it is built to potentially become one and could be considered a canonical AI plugin for WordPress:

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
4. AI Playground code modal showing PHP and JavaScript code for the current prompt and configuration

== Changelog ==

= 0.7.1 =

**Enhancements:**

* Add support for multimodal input for Chrome and Edge built-in AI. ([294c86a](https://github.com/felixarntz/ai-services/commit/294c86aab746be0318c0601c5238468d61fa3672))
* Update provider model sorting to be more flexible and future-proof. ([584d8f3](https://github.com/felixarntz/ai-services/commit/584d8f360e54cc098f17ad0482f96634600d9e67))
* Rename `ais_load_services_capabilities` action to `ai_services_load_services_capabilities`. ([1557214](https://github.com/felixarntz/ai-services/commit/1557214e8982997849ad2e7a70e67facd57260d4))

**Bug Fixes:**

* Fix missing image generation support for Nano Banana. ([fde4e4c](https://github.com/felixarntz/ai-services/commit/fde4e4c2c7fdb32bcaa279fd03b46c8675f89d7d))
* Fix sanitization bug for float values in generation config. ([f5dc61b](https://github.com/felixarntz/ai-services/commit/f5dc61ba22607098aef50fac22da7ce2d02bb102))

= 0.7.0 =

**Features:**

* Add support for Mistral AI with text generation, multimodal input, and function calling. ([5e6d4fe](https://github.com/felixarntz/ai-services/commit/5e6d4fea49ec5ba807f265b259aaee4a882c4220))
* Add support for Perplexity AI (Sonar models) with text generation, multimodal input, and web search. ([b793955](https://github.com/felixarntz/ai-services/commit/b793955330796615ba4f6b0bbb19d93ebcd6f9d1))
* Add support for xAI (Grok models) with text generation, multimodal input, function calling, and web search, as well as image generation. ([8407c9c](https://github.com/felixarntz/ai-services/commit/8407c9c2399f75777a75df07bf89614eae72305f))
* Add support for text-to-speech, via new TEXT_TO_SPEECH AI capability and necessary infrastructure in PHP and JavaScript. Props [sethrubenstein](https://github.com/sethrubenstein). ([9d3652e](https://github.com/felixarntz/ai-services/commit/9d3652eee9e31aece4ef870fc4363423907eaea9), [#31](https://github.com/felixarntz/ai-services/issues/31))
* Add support for text-to-speech to OpenAI service and its applicable models. ([f73341a](https://github.com/felixarntz/ai-services/commit/f73341a5deacaf65635438b0ca4d11775f8e5191))
* Add support for multimodal audio output (speech generation) for applicable Google and OpenAI models. ([28651c2](https://github.com/felixarntz/ai-services/commit/28651c2a7ea298869680d3c9e6dbe04a66778608))
* Add support for web search, supported by Anthropic and Google, usable also via the AI Playground and the WP-CLI command. ([62c63bc](https://github.com/felixarntz/ai-services/commit/62c63bcd895da71abe8cbd0fbac7eaa407129c92))
* Introduce `ai_services_skip_api_request` filter to effectively enable client-side only mode using the browser built-in models, e.g. for unauthenticated visitors. ([2224db1](https://github.com/felixarntz/ai-services/commit/2224db122f8526a6d56b227b4bd2df5316c5511c))

**Enhancements:**

* Allow using text-to-speech capabilities in the AI Playground. ([daefd02](https://github.com/felixarntz/ai-services/commit/daefd0267e687b54c18f50b37065be66db94ce41))
* Allow selecting which output modalities to request in the AI Playground (for models which support multimodal output). ([3997520](https://github.com/felixarntz/ai-services/commit/39975200bf1edc18a541bc0eb8ce4c51692e91a1))
* Support chat history for built-in browser model, and update to latest API for providing system instruction. ([f522a30](https://github.com/felixarntz/ai-services/commit/f522a30ebbe7eef9aee9f0d1e19eebae7ccafd5e))
* Support passing videos, including YouTube URLs, to Google API as multimodal input. ([c0b913c](https://github.com/felixarntz/ai-services/commit/c0b913cea71e7e85c5925ec7a594c330764ac17e))
* Implement new base classes and traits for OpenAI compatible generative models, to drastically reduce boilerplate for supporting different services. ([9515864](https://github.com/felixarntz/ai-services/commit/951586481493f322ffdbaf3b7831f468bd11f246))
* Add `ai_services_request_timeout` filter to centrally customize AI API request timeout. Props [EvanHerman](https://github.com/EvanHerman). ([7a9c302](https://github.com/felixarntz/ai-services/commit/7a9c30272607e3a0752f2e56bf063d1c4b62b6e9), [#38](https://github.com/felixarntz/ai-services/issues/38))
* Add WordPress Playground `blueprint.json` for plugin preview. Props [fellyph](https://github.com/fellyph). ([#42](https://github.com/felixarntz/ai-services/pull/42))
* Enhance settings UI by better visual separation of fields through additional spacing. ([e0434a5](https://github.com/felixarntz/ai-services/commit/e0434a549526fff31095bb42a67eb2d2f5467635))
* Show Edge built-in AI model name in AI Playground for browser built-in AI service when using Edge. Props [sethrubenstein](https://github.com/sethrubenstein). ([48b8807](https://github.com/felixarntz/ai-services/commit/48b8807dfd129e66dffa39936982d68893a54aaf), [#35](https://github.com/felixarntz/ai-services/issues/35))
* Refresh model preferences for Anthropic, Google, and OpenAI. ([7991312](https://github.com/felixarntz/ai-services/commit/79913122cae71fbb558ce591befd1c6f690bfa40))
* Implement mock AI service for testing. Props [swissspidy](https://github.com/swissspidy). ([4d3f236](https://github.com/felixarntz/ai-services/commit/4d3f236e8de4cbe34c01eae1b2256fb9a0f8ff66), [77e4f96](https://github.com/felixarntz/ai-services/commit/77e4f96c2cb65bfd8cf8baa4a0b3436fd88bd8b2), [#32](https://github.com/felixarntz/ai-services/issues/32))
* Improve system instruction for experimental built-in chatbot. ([135f1a9](https://github.com/felixarntz/ai-services/commit/135f1a98c14891d873198fdef9465ca6b58d137f), [6150a80](https://github.com/felixarntz/ai-services/commit/6150a8015eeead1b4e9d9fb2aed809102ee0c88b))
* Drastically simplify implementing a service class by implementing `Abstract_AI_Service` class and `With_API_Client_Trait`. ([2678cfb](https://github.com/felixarntz/ai-services/commit/2678cfbb3a8e1244c57d717242d961be09718399))
* Reduce complexity of implementing API connection drastically via new `Generic_AI_API_Client` class abstracting functionality commonly shared across different providers. ([6930bc0](https://github.com/felixarntz/ai-services/commit/6930bc0786b70f724df39c102df304dbedc70eff))
* Use `With_API_Client` and corresponding trait in generative AI model classes. ([a4b8fc6](https://github.com/felixarntz/ai-services/commit/a4b8fc64b6f341b110c87c418b4d0ff79cb33955))
* Simplify model implementation by introducing traits for the individual model params. ([12d725f](https://github.com/felixarntz/ai-services/commit/12d725fdc959c3d85ae9b6065a1992b7a890f513))
* Introduce `Service_Metadata` type to centralize handling of service metadata and simplify API. ([6966934](https://github.com/felixarntz/ai-services/commit/6966934f3abb83280cc5f4d12fae9ced39b98687))
* Introduce `Model_Metadata` type to centralize handling of model metadata, using the object in the list_models() method. ([7ed37e1](https://github.com/felixarntz/ai-services/commit/7ed37e18db5fd66f7fad75b65cba35b88ada9786))
* Implement service type awareness (cloud, server, client) via new enums in PHP and JavaScript and integration as part of service metadata. ([ad26527](https://github.com/felixarntz/ai-services/commit/ad26527dc34df92ed1cb35154f2cebb6269099e6))
* Include service capabilities in metadata handling, decoupling it from the service being available and simplifying service implementation. ([cb5891f](https://github.com/felixarntz/ai-services/commit/cb5891f2af73cf0839ddfaae771f18476fee26d2))
* Make model classes aware of their metadata for easier access when working with the model class. ([3b3fab4](https://github.com/felixarntz/ai-services/commit/3b3fab4bf4349dd33b5ead54c8721f5a4192744f))
* Make service classes and interface aware of the service's metadata and make service creator callbacks future-proof via new `Service_Registration_Context` class. ([7401650](https://github.com/felixarntz/ai-services/commit/7401650838870e9f68110b6ccb01c80ac4ab3fb9))
* Implement new `Modality` enum in PHP and JavaScript. ([51169f3](https://github.com/felixarntz/ai-services/commit/51169f394f7985b65c914bb634ee16dcec83fb23))
* Decouple concrete model implementations from WordPress specific functions. ([f2dd6c4](https://github.com/felixarntz/ai-services/commit/f2dd6c404bff632906db8fd36191512e4245a57a))
* Allow API version string to be empty in `Generic_AI_API_Client` implementation. ([985781e](https://github.com/felixarntz/ai-services/commit/985781e228a1351185822a36f675ef8c0ee4ffa9))
* Update service REST API schema to use model metadata schema for its model information included. ([1b2e2bb](https://github.com/felixarntz/ai-services/commit/1b2e2bb590d424eb87953619e30317a4a55eddb9))
* Remove Google-specific hack in service agnostic REST route. ([e26e804](https://github.com/felixarntz/ai-services/commit/e26e80445d2410fb7fef1689ed0fd3eb1cfdbfd0))
* Migrate entire client-side JavaScript codebase to TypeScript. ([7d0d50f](https://github.com/felixarntz/ai-services/commit/7d0d50f6491eacd6802f6808892673ee6c2a9b12), [3c7bd9e](https://github.com/felixarntz/ai-services/commit/3c7bd9efb1ae242b3e633639e4622c8345420381), [0f8f464](https://github.com/felixarntz/ai-services/commit/0f8f46409cb1c16d83b887dd74aa87cadac5d3c7), [bfd43a9](https://github.com/felixarntz/ai-services/commit/bfd43a9913abd43ce66bfdc39cc58a11952de02e), [b119e70](https://github.com/felixarntz/ai-services/commit/b119e70d8973f6c3cbb47dc1c5051065a2df9282), [02066bb](https://github.com/felixarntz/ai-services/commit/02066bb95c8fe5964a3bfcbecfc5d7f771cf0a1a), [28704d9](https://github.com/felixarntz/ai-services/commit/28704d9fc01d7f99f6fbb72e63c4dfeaae4d4726), [6a82ca5](https://github.com/felixarntz/ai-services/commit/6a82ca50226a2754f0acc9eca25c99848e33751f), [2c936b4](https://github.com/felixarntz/ai-services/commit/2c936b4badf04bc2ab9395c9cd68cc3b98ab1e50), [924bb59](https://github.com/felixarntz/ai-services/commit/924bb59f0517f7b782f0c0ca2becf93d6f6a57fd))
* Update to latest Chrome built-in browser specification, using recommended availability API instead of capabilities API. ([d32788e](https://github.com/felixarntz/ai-services/commit/d32788e7ae1a701f965f52a933235702e917e87d))
* Migrate `components` package to become `interface` package. ([5f796c8](https://github.com/felixarntz/ai-services/commit/5f796c8a4785745e13d1117053198754ed432aab))
* Rename `settings-store` package to `settings`. ([a44320f](https://github.com/felixarntz/ai-services/commit/a44320f520e6965b221ea36ed34f2676d207d7b4))
* Improve settings store implementation for stability and flexibility. ([b7ec1c5](https://github.com/felixarntz/ai-services/commit/b7ec1c5cd19dd06c59e9a7f82c93b8d1c94c225a))
* Enhance API error reporting in API client trait. ([9232c60](https://github.com/felixarntz/ai-services/commit/9232c600672cbf93272b9087f4b5c56ef519529f))
* Use `logError` utility instead of `console.error` in ai store and settings store. ([03bacf6](https://github.com/felixarntz/ai-services/commit/03bacf6a6c88ea2003d310232ce179bad0beefa9))
* Implement `errorToString` utility function to improve error handling through client-side UI. ([ae5ad8b](https://github.com/felixarntz/ai-services/commit/ae5ad8b158515c35f1d147cce5105189b222f805))
* Implement API client helper method for API scoped bad request exceptions. ([6f66fb0](https://github.com/felixarntz/ai-services/commit/6f66fb040ec93bfacfa8e3bdf482fa5068349a09))
* Remove now superfluous PHP and JS methods to get service name and credentials URL, in favor of new methods to get service metadata. ([33a9a4b](https://github.com/felixarntz/ai-services/commit/33a9a4b64574c5e09fb0b8bd5b8e3cf6ee49efcd))
* Change deprecated `Generation_Config` class to become an interface and implement `Abstract_Generation_Config` base class to simplify implementation. ([2410b7b](https://github.com/felixarntz/ai-services/commit/2410b7ba1f74bc189348730575c5089f5a7422cc))
* Remove deprecated `Anthropic_AI_Model`, `Google_AI_Model`, and `OpenAI_AI_Model` classes in favor of their more specific implementations. ([1d8b731](https://github.com/felixarntz/ai-services/commit/1d8b7314ac42514db1e1dbe39f2bae7371e923a3))
* Remove previously deprecated `ais-ai-store` asset alias. ([c54e5d5](https://github.com/felixarntz/ai-services/commit/c54e5d57391b06f8f3bd0273d409782275143a44))

**Bug Fixes:**

* Fix bug where some Google models could be missing from the API response and therefore seem to be unavailable. ([75b8ce2](https://github.com/felixarntz/ai-services/commit/75b8ce21a5d719118b68bcca9794655aa8c6077b))
* Fix OpenAI API compatible models to include reasoning output, also resolving potential streaming errors. ([7fd69c9](https://github.com/felixarntz/ai-services/commit/7fd69c9bc8ee8c1a4dc06e973f2c76bcd3310e52))
* Fix logic bug in Abstract_Enum that could cause conflicts between different enums. ([f6bcbb5](https://github.com/felixarntz/ai-services/commit/f6bcbb5365e530a1c69d002db04b40d4ecf81f84))
* Fix service registration handling to only set up authentication for CLOUD services. ([a2ef6b0](https://github.com/felixarntz/ai-services/commit/a2ef6b026d303a36f25d67136bb22b9ca86df3ce))
* Fix bug in `Abstract_Generation_Config` default handling. ([1a71735](https://github.com/felixarntz/ai-services/commit/1a71735c969dd39550af3f0bae75eb5a5e51dc9f))
* Fix `getApiKey` selector return type and `ApiKeyControl` props. ([29b416a](https://github.com/felixarntz/ai-services/commit/29b416a4a683864a79143ce669506772dcc1159e))
* Fix PHP 8.4 deprecated implicit nullable parameters and use constant for Anthropic API version. Props [JosephGabito](https://github.com/JosephGabito). ([#40](https://github.com/felixarntz/ai-services/pull/40), [06571b7](https://github.com/felixarntz/ai-services/commit/06571b7fc1b035fe3bd122c1cafb4524314d6b06))
* Fix AI API image generation request error handling in JS. ([387d42a](https://github.com/felixarntz/ai-services/commit/387d42a374739bd076f4b775ecbb78ad68059085))
* Fix typo in `Text_Generation_Config` parameter description. ([52956e1](https://github.com/felixarntz/ai-services/commit/52956e1fca845d1d5586aa2479ee7acf4e679ddf))
* Fix minor code flaw with incorrect array size. ([4bc9e1b](https://github.com/felixarntz/ai-services/commit/4bc9e1bf55d201a268bfe7150b113b78164d3e94))

**Documentation:**

* Document how to transform text to speech in PHP and JavaScript. ([515e696](https://github.com/felixarntz/ai-services/commit/515e69698fc6d5e137832e6a85940ddc23b4b4f7))
* Document TypeScript code (for human and LLM consumption). ([6685047](https://github.com/felixarntz/ai-services/commit/66850478ab5b7bc9e80e8a6fc804506e3456d3b2))
* Add WordPress Playground link to `README.md`. Props [iftakharul-islam](https://github.com/iftakharul-islam). ([#43](https://github.com/felixarntz/ai-services/pull/43))

= 0.6.5 =

**Bug Fixes:**

* Fix iteration over possibly undefined value. ([bbcf37c](https://github.com/felixarntz/ai-services/commit/bbcf37c7bafa11b8b3b53241c5200cb798d884ae))

= 0.6.4 =

**Bug Fixes:**

* Fix possible error when resolving `getServices()` selector due to browser built-in AI not being present. Props [ocean90](https://github.com/ocean90). ([269bde4](https://github.com/felixarntz/ai-services/commit/269bde42ad9c32c773986af6952680ef4c85022d))
* Sanitize AI Playground `getAdditionalCapabilities()` selector output against available capabilities. ([7acd7d0](https://github.com/felixarntz/ai-services/commit/7acd7d0c11109ee8f46b8e06f403b8bfaff884fb))
* Clarify AI Playground notice message for when no applicable AI services are available. ([91d6ab8](https://github.com/felixarntz/ai-services/commit/91d6ab89760c922642219fa6c8ac6a30e762df24))

= 0.6.3 =

**Bug Fixes:**

* Fix WordPress.org deploy workflow permission. ([19efbb8](https://github.com/felixarntz/ai-services/commit/19efbb82b8f333af285ea230f8204557495e1cc6))
* Harden GitHub workflow permissions. ([04916b2](https://github.com/felixarntz/ai-services/commit/04916b2065cb369cbcfc021e5f7b71861d71c364))

= 0.6.2 =

**Enhancements:**

* Support new `gpt-image-1` model for image generation. ([66afb78](https://github.com/felixarntz/ai-services/commit/66afb78f773f8a588f0a6578b167967cc30e0085))
* Generate build provenance attestation. Props [johnbillion](https://github.com/johnbillion). ([#30](https://github.com/felixarntz/ai-services/pull/30))

**Bug Fixes:**

* Avoid showing PHP code in AI Playground modal when using client-side AI service (like Chrome built-in AI). ([b25e392](https://github.com/felixarntz/ai-services/commit/b25e392108c213f0bf7d747b9c509bbd8b02e2e2))
* Fix issue in `SettingsCards` component with `useSelect` not returning consistent result. ([24ab0cf](https://github.com/felixarntz/ai-services/commit/24ab0cf62134ddcfae061b9cdcac5b39f4ce354a))

= 0.6.1 =

**Bug Fixes:**

* Ensure regular `gemini-2.0-flash-exp` model is annotated with multimodal output. ([8f2d7ea](https://github.com/felixarntz/ai-services/commit/8f2d7ea65057a6736f504efbf7a77fab5f95b976))

= 0.6.0 =

**Features:**

* Add support for multimodal output (text and images) and implement it in new Google Gemini model that supports it. Props [swissspidy](https://github.com/swissspidy). ([33d7f16](https://github.com/felixarntz/ai-services/commit/33d7f16bc81c84b86e1f528bff888beb266d917e), [#27](https://github.com/felixarntz/ai-services/pull/27))
* Add the ability to view fully functional PHP and JavaScript code for each prompt in the AI Playground. ([f99f964](https://github.com/felixarntz/ai-services/commit/f99f964fa9e496f418e2723609c1117f3c0e13cf))

**Enhancements:**

* Add support for including multiple images in prompts on the AI Playground. Props [fellyph](https://github.com/fellyph). ([e943cf1](https://github.com/felixarntz/ai-services/commit/e943cf1f2a8f83511eb513f8df4db66abddc29c5), [#28](https://github.com/felixarntz/ai-services/issues/28))
* Update Chrome built-in AI implementation to support latest entry points starting with Chrome 136. ([c4ff704](https://github.com/felixarntz/ai-services/commit/c4ff704cde8682aac95766d3e83ad6965cc203f6))
* Update model name used for Chrome built-in AI. ([58850d2](https://github.com/felixarntz/ai-services/commit/58850d2ccff0e18f949f63f16c933b5414ce5b73))
* Implement reusable `ApiKeyControl` component in JavaScript and `API_Key_Control` class in PHP to allow easy rendering of API key controls in custom settings UI. ([2d23b0a](https://github.com/felixarntz/ai-services/commit/2d23b0aaa1b775876b0915559abea4e40e179074))
* Support content with multiple files (e.g. images) to be uploaded at the same time in AI Playground. ([31f830f](https://github.com/felixarntz/ai-services/commit/31f830fd4cadb66c35c25cab230d28c772120abc))
* Enhance attachment filename generation for AI Playground uploaded files to include source. ([0d399af](https://github.com/felixarntz/ai-services/commit/0d399af85c1513e9e31c5f4bd2572b0b8b5213c3))
* Implement new helper function in PHP and JavaScript to create a content object from a prompt and multiple attachments. ([0138cb3](https://github.com/felixarntz/ai-services/commit/0138cb321be9214292c7770bd68b29154314e2f3))
* Implement helper functions to transform base64 data URLs in PHP and JavaScript. ([fe83f7b](https://github.com/felixarntz/ai-services/commit/fe83f7b3912b6a8c257a35abc869e994a511be2c))
* Reserve more vertical space for modals in AI Playground on larger viewports. ([2c4e3aa](https://github.com/felixarntz/ai-services/commit/2c4e3aaca2efc242e666c5db3cf987e55abdf166))
* Allow passing through custom model parameters as part of generationConfig API field for Google AI models. ([ff4ed12](https://github.com/felixarntz/ai-services/commit/ff4ed12a7adc418c6bd48f5cba722dc77ad7cef3))
* Use `__next40pxDefaultSize` prop in compliance with WordPress 6.8 requirements. ([28a1f60](https://github.com/felixarntz/ai-services/commit/28a1f606c337186110573738a248e7f14589596b))
* Remove usage of WordPress functions and expect interfaces rather than concrete implementations in core AI API implementation to decouple from WordPress. ([8fd6d91](https://github.com/felixarntz/ai-services/commit/8fd6d91d0be3eda0fa9d9f7bbff176368fc23386), [6f2a7c2](https://github.com/felixarntz/ai-services/commit/6f2a7c25c128a6bc7240e57bfc3b0129b23672d4), [081e406](https://github.com/felixarntz/ai-services/commit/081e4064908e26d625a4c555eedadda37549db2a), [3dd51c2](https://github.com/felixarntz/ai-services/commit/3dd51c21c4afe6c556007fc8729081d22e580498), [#29](https://github.com/felixarntz/ai-services/issues/29))
* Remove deprecated `aiServices.aiStore` from AI package. ([372d750](https://github.com/felixarntz/ai-services/commit/372d750b3462c7f4227b575d69605ba90d76d171))
* Move AI store files into a separate directory within the package. ([6d18048](https://github.com/felixarntz/ai-services/commit/6d18048028b5cdcab3b1e3efb88c33c2771bdce3))

**Bug Fixes:**

* Fix issue in `Interface` component with `useSelect` not returning consistent result. ([cc31d7a](https://github.com/felixarntz/ai-services/commit/cc31d7a019ce093f01287523faf6c6dd3081e876))
* Fix some styling issues in `Parts` component. ([767fd5b](https://github.com/felixarntz/ai-services/commit/767fd5b3d41b6a9d6061599db2a6b838132f1d0f))
* Fix confusing limitation of function response parts only allowing objects as response. Props [swissspidy](https://github.com/swissspidy). ([650c0d2](https://github.com/felixarntz/ai-services/commit/650c0d2111187df4e550b78a8130f25953342d35))
* Fix AI Playground footer status text to show model name instead of model slug. ([d5ee4e0](https://github.com/felixarntz/ai-services/commit/d5ee4e07b71113c50faed376cdf71695936b7339))

**Documentation:**

* Document how to use multimodal output (text and images for now). ([e7a0d0e](https://github.com/felixarntz/ai-services/commit/e7a0d0ed57968b9fd9dd9ebd60da7db54aaee3b7))
* Add documentation about technical architecture. ([ca75eaa](https://github.com/felixarntz/ai-services/commit/ca75eaaf5dceefd378b6ad3e323c9e624e21095c))
* Provide documentation on how to render AI Services API key settings field in custom UI anywhere in WP Admin. ([557eca6](https://github.com/felixarntz/ai-services/commit/557eca61136aefda5d6a300c8063b29d86cdb8ac))
* Update documentation regarding Chrome built-in AI. ([a95eb62](https://github.com/felixarntz/ai-services/commit/a95eb62627cc8056c1bc5abc9842e8e225e4c2f8))

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
