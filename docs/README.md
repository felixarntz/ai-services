---
title: AI Services
permalink: /
---

The AI Services plugin makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

| AI Services: Settings screen | AI Services: Playground screen |
| ------------- | ------------- |
| ![The AI Services settings screen where users can paste their AI service credentials](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-1.png)  | ![Multimodal text generation in the AI Playground where users can explore the different AI model capabilities](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-2.png)  |

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

## Why?

* A centralized AI infrastructure **facilitates user choice**. Users may prefer certain AI services over other ones, and for many common tasks, either of the popular AI services is suitable. Having a common API regardless of the AI service allows leaving the choice to the user, rather than the plugin author.
* Since the centralized AI infrastructure comes with a common API that works the same for every AI service, it means **plugin developers don't have to spend as much time familiarizing themselves with different services**, at least when it comes to simple tasks. For tasks where certain services may have advantages over others, there is still flexibility to focus on a specific AI service.
* It also means **no more reinventing the wheel**: Since most AI services do not provide PHP SDKs for their APIs, many times this means WordPress plugins that want to leverage AI have to implement their own layer around the service's API. Not only is that time consuming, it also distracts from working on the actual (AI driven) features that the plugin should offer to its users. In fact this directly facilitates the user choice aspect mentioned, as having APIs for various AI services already provided means you can simply make those available to your plugin users.
* Having central AI infrastructure available **unlocks AI capabilities for smaller plugins or features**: It may not be worth the investment to implement a whole AI API layer for a simple AI driven feature, but when you already have it available, it can lead to more plugins (and thus more users) benefitting from AI capabilities.
* Last but not least, a central AI infrastructure means **users will only have to configure the AI API once**, e.g. paste their API keys only in a single WordPress administration screen. Without central AI infrastructure, every plugin has to provide its own UI for pasting API keys, making the process more tedious for site owners the more AI capabilities their site uses.

## Code examples for using the API

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
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature
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

Refer to the [plugin documentation](./Documentation.md) for granular examples including explainers.

For complete examples such as entire plugins built on top of the AI Services infrastructure, please see the [examples directory on GitHub](https://github.com/felixarntz/ai-services/tree/main/examples).
