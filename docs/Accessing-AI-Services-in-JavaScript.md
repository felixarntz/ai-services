---
title: Accessing AI Services in JavaScript
layout: page
---

This section provides some documentation on how to access AI services in JavaScript. This is relevant for any plugins that would like to generate content via client-side logic.

## Enqueueing the JavaScript API

Before being able to use the AI Services JavaScript API, you need to make sure it is loaded. This can be done in PHP by enqueueing the script with handle `ais-ai`, which contains the API. When the AI Services plugin is active, the `ais-ai` script is unconditionally registered so that it is easy to enqueue, but it should only be enqueued when it's actually needed to avoid unused JavaScript which can negatively impact performance.

Here is an example PHP function (e.g. hooked into `wp_enqueue_scripts` or `admin_enqueue_scripts`) to show how you could have your own script require on the AI Services JavaScript API script, so that they would always be enqueued together:

```php
function myplugin_enqueue_ai_script() {
	// Check if the AI Services plugin is active.
	if ( ! function_exists( 'ai_services' ) ) {
		return;
	}
	wp_enqueue_script(
		'myplugin-ai-script',
		plugin_dir_url( __FILE__ ) . 'js/ai-script.js',
		array( 'ais-ai' )
	);
}
```

Alternatively, you may want to progressively enhance your script's functionality with AI instead of _requiring_ it. If so, you should conditionally add the AI Services JavaScript API as a dependency so that it is only loaded when available, but your plugin's script would be loaded regardless. Here is an example of what that could look like:

```php
function myplugin_enqueue_script_with_optional_ai() {
	$dependencies = array();

	// Add AI script dependency only if the AI Services plugin is active.
	if ( function_exists( 'ai_services' ) ) {
		$dependencies[] = 'ais-ai';
	}

	wp_enqueue_script(
		'myplugin-ai-script',
		plugin_dir_url( __FILE__ ) . 'js/ai-script.js',
		$dependencies
	);
}
```

If you choose to go with the latter approach, you would need to check in your JavaScript file whether the AI Services JavaScript API is loaded. An easy way to do that would be to check for whether the `aiServices.ai` global is available:

```js
if ( window.aiServices && window.aiServices.ai ) {
	// Run AI-dependent logic.
}
```

But now let's dive into the actual JavaScript APIs!

## Introduction to the JavaScript API

The canonical entry point to all of the JavaScript public APIs is the "ai-services/ai" datastore registered in WordPress's `wp.data` registry. The store object exposes various selectors that should be used to access the AI services. The concrete usage is best outlined by examples. For illustrative purposes, here is a full example of generating text content using the `google` service:

```js
const { isServiceAvailable, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( isServiceAvailable( 'google' ) ) {
	const service = getAvailableService( 'google' );
	try {
		const candidates = await service.generateText(
			'What can I do with WordPress?',
			{ feature: 'my-test-feature' }
		);
	} catch ( error ) {
		// Handle the error.
	}
}
```

Alternatively to hard-coding `'ai-services/ai'` for the store name, you can reference `aiServices.ai.store` from the `aiServices` JavaScript global.

Note that any of the selectors may temporarily return `undefined`, which should be interpreted as that the services are being loaded. **A return value of `undefined` does not mean that something was not found.** For that, the value `null` is used.

Additionally, calling `service.generateText` the above code example is a short-hand for retrieving the model using `service.getModel` and then calling `model.generateText`. For reference, the following code example does exactly the same as above, but more verbosely. With that approach, instead of passing the model parameters to the `generateText` method, you pass them to the `getModel` method, and the returned model will then use them for any generation tasks invoked on it. This approach can make sense if you want to reuse the same model for multiple generation tasks.

```js
const enums = aiServices.ai.enums;
const { isServiceAvailable, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( isServiceAvailable( 'google' ) ) {
	const service = getAvailableService( 'google' );
	try {
		const model = service.getModel(
			{
				feature: 'my-test-feature',
				capabilities: [ enums.AiCapability.TEXT_GENERATION ],
			}
		);
		const candidates = await model.generateText(
			'What can I do with WordPress?'
		);
	} catch ( error ) {
		// Handle the error.
	}
}
```

These short-hands exist for all generation methods on the model instance. You may instead call the respective method on the service instance, passing the model parameters as additional argument to it.

For more specific examples with explanations, see the following sections. In those examples, the short-hand syntax will be used.

## Retrieving an available AI service

To use any AI capabilities, you first have to retrieve an available AI service. Which AI services are available depends on the end user, e.g. the site owner. They are free to configure any of the registered AI services with credentials. While many sites will likely only have one AI service configured, some sites may have all services configured (e.g. if the site owner has subscriptions with all of them or if they are free to use).

### Using a specific AI service

You can pass the slug of a specific AI service to the `getAvailableService()` selector. Before doing so, it is recommended to first check whether the service available (i.e. configured by the user with valid credentials), using the `isServiceAvailable()` selector:

```js
const { isServiceAvailable, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( isServiceAvailable( 'google' ) ) {
	const service = getAvailableService( 'google' );
	// Do something with the AI service.
}
```

Alternatively, if you don't want to use the `isServiceAvailable()` selector, you need to check whether the `getAvailableService()` selector does not return `null`:

```js
const { getAvailableService } = wp.data.select( 'ai-services/ai' );
const service = getAvailableService( 'google' );
if ( service !== null ) {
	// Do something with the AI service.
}
```

### Using any available AI service

For many AI use-cases, relying on different AI services may be feasible. For example, to respond to a simple text prompt, you could use _any_ AI service that supports text generation. If so, it is advised to not require usage of a _specific_ AI service, so that the end user can configure whichever service they prefer and still use the relevant AI functionality you're implementing in your plugin. You can do so by passing arguments to the `hasAvailableServices()` and `getAvailableService()` selectors for which capabilities you require for what you intend to do (for example to generate text):

```js
const enums = aiServices.ai.enums;

const SERVICE_ARGS = { capabilities: [ enums.AiCapability.TEXT_GENERATION ] };
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices( SERVICE_ARGS ) ) {
	const service = getAvailableService( SERVICE_ARGS );
	// Do something with the AI service.
}
```

Alternatively, if you don't want to use the `hasAvailableServices()` selector, you need to check whether the `getAvailableService()` selector does not return `null`:

```js
const enums = aiServices.ai.enums;

const SERVICE_ARGS = { capabilities: [ enums.AiCapability.TEXT_GENERATION ] };
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
const service = getAvailableService( SERVICE_ARGS );
if ( service !== null ) {
	// Do something with the AI service.
}
```

In some instances, you may have a preference for a few specific AI services that work well for your AI use-case - in those cases you can pass multiple slugs, to retrieve whichever one the user has configured:

```js
const SERVICE_ARGS = { slugs: [ 'google', 'openai' ] };
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices( SERVICE_ARGS ) ) {
	const service = getAvailableService( SERVICE_ARGS );
	// Do something with the AI service.
}
```

## Generating text content using an AI service

Once you have retrieved an AI service, you can use it to get a text response to a prompt. You need to call the `generateText` method of the service. This method will only function if the service implements the "text_generation" capability - which all built-in services do, but there may be further custom AI services registered some of which may only support other capabilities such as "image_generation".

The recommended way to refer to any AI capabilities is by using the available constants on `aiServices.ai.enums.AiCapability` from the `aiServices` JavaScript global.

### Using the preferred model for certain capabilities

Here is an example of how to generate the response to a simple prompt, using the preferred model for text generation. Assume that the `service` variable contains an available service instance that supports `enums.AiCapability.TEXT_GENERATION` (e.g. based on the previous examples):

```js
const enums = aiServices.ai.enums;

try {
	const candidates = await service.generateText(
		'What can I do with WordPress?',
		{
			feature: 'my-test-feature',
			capabilities: [ enums.AiCapability.TEXT_GENERATION ],
		}
	);
} catch ( error ) {
	// Handle the error.
}
```

Note: When calling the `generateText()` method, specifying the `enums.AiCapability.TEXT_GENERATION` capability is optional, as that is implied by calling that particular method.

### Using a custom model

You can also select a specific model from a service. Of course the available models differ per service, so if you intend to do use a custom model, you will need to handle this per service. If the service instance used may be from different service, you can use the `getServiceSlug()` method of the service to determine its slug. In this example, let's assume the `service` variable contains one of the `google` or `openai` services:

```js
const model = service.getServiceSlug() === 'openai' ? 'gpt-4o' : 'gemini-1.5-pro';
try {
	const candidates = await service.generateText(
		'What can I do with WordPress?',
		{
			feature: 'my-test-feature',
			model,
		}
	);
} catch ( error ) {
	// Handle the error.
}
```

Note: Alongside the model property in the object, you may pass other configuration arguments supported by models of the respective service.

### Sending multimodal prompts

As mentioned in the [technical concepts section about sending data to AI services](./Technical-Concepts-of-AI-Services.md#sending-data-to-AI-services), passing a string to the `generateText()` method is effectively just a shorthand syntax for the more elaborate content format. To pass more elaborate content as a prompt, you can use content objects or part arrays. For example, if the AI service supports multimodal content, you can ask it to describe a provided image:

```js
const enums = aiServices.ai.enums;

const content = {
	role: enums.ContentRole.USER,
	parts: [
		{
			text: 'Briefly describe what is displayed in the following image using a single sentence.'
		},
		{
			mimeType: 'image/jpeg',
			fileUri: 'https://example.com/image.jpg'
		}
	]
};
try {
	const candidates = await service.generateText(
		content,
		{
			feature: 'my-test-feature',
			capabilities: [
				enums.AiCapability.MULTIMODAL_INPUT,
				enums.AiCapability.TEXT_GENERATION,
			],
		}
	);
} catch ( error ) {
	// Handle the error.
}
```

You can also pass an array of content objects. In this case, this will be interpreted as the history including previous message exchanges from the same chat.

### Processing text responses

The `generateText()` model method returns an array of candidate objects that contains the alternative response candidates - usually just one, but depending on the prompt and configuration there may be multiple alternatives.

Every candidate in the list is an object, which allows you to access its actual content as well as metadata about the particular response candidate.

For example, you can use code as follows to retrieve the text content of the first candidate.

```js
let text = '';
for ( const part of candidates[ 0 ].content.parts ) {
	if ( part.text ) {
		if ( text ) {
			text += '\n\n';
		}
		text += part.text;
	}
}
```

This code example realistically should work in 99% of use-cases. However, there may be a scenario where the first candidate only contains non-text content. In that case the code example above would result in an empty string. Therefore, technically speaking it is the safest approach to first find a candidate that has any text content.

As this can be tedious, the AI Services API provides a set of helper methods to make it extremely simple. You can access the helper methods via [`aiServices.ai.helpers`](https://github.com/felixarntz/ai-services/tree/main/src/ai/helpers.js) from the `aiServices` JavaScript global.

The following example shows how you can accomplish the above in a safer, yet simpler way:

```js
const helpers = aiServices.ai.helpers;
const text = helpers.getTextFromContents(
	helpers.getCandidateContents( candidates )
);
```

### Streaming text responses

Alternatively to using the `generateText()` method, you can use the `streamGenerateText()` method so that the response is streamed. This can help provide more immediate feedback to the user, since chunks with partial response candidates will be available iteratively while the model still processes the remainder of the response. In other words, you can print the text from these chunks right away, so that it almost looks as if the generative AI model was typing it. Especially for prompts that expect a larger response (e.g. more than one paragraph), streaming the response can have major benefits on user experience.

The `streamGenerateText()` method takes the same parameters as the `generateText()` method. Instead of returning the final candidates instance though, it returns a generator that yields the partial candidates chunks. This generator can be used to iterate over the chunks as they arrive.

The following example shows how you could use streaming:

```js
const enums = aiServices.ai.enums;
const helpers = aiServices.ai.helpers;

try {
	const candidatesGenerator = await service.streamGenerateText(
		'What can I do with WordPress?',
		{
			feature: 'my-test-feature',
			capabilities: [ enums.AiCapability.TEXT_GENERATION ],
		}
	);

	for await ( const candidates of candidatesGenerator ) {
		const text = helpers.getTextFromContents(
			helpers.getCandidateContents( candidates )
		);

		// Append text chunk to user-facing response.
	}
} catch ( error ) {
	// Handle the error.
}
```

### Using the browser built-in client-side AI

In JavaScript the AI Services plugin allows using another service `browser`, additionally to using the third party API based AI services. This service relies on [Chrome's built-in AI APIs](https://developer.chrome.com/docs/ai/built-in-apis), which allow using AI in the browser, running on the user's device, which can be a great option for certain use-cases as it does not require paying for API access and does not involve sending your prompts to an external third-party API.

Note that these APIs are still in an experimental stage and are not yet rolled out completely. If you use Chrome and already have access to the APIs, you can use them through the AI Services plugin just like any other service's APIs. To see whether you have access, check if `window.LanguageModel || window.ai.languageModel` is available in your browser console. If not, you can [request to join the Early Preview Program](http://goo.gle/chrome-ai-dev-preview-join).

### Customizing the default text generation configuration

When retrieving a model using the `getModel()` method, it is possible to provide a `generationConfig` argument to customize the model configuration. The `generationConfig` key needs to contain an object with configuration arguments. These arguments are normalized in a way that works across the different AI services and their APIs.

Additionally to `generationConfig`, you can pass a `systemInstruction` argument if you want to provide a custom instruction for how the model should behave. By setting a system instruction, you give the model additional context to understand its tasks, provide more customized responses, and adhere to specific guidelines over the full user interaction with the model.

Here is a code example using both `generationConfig` and `systemInstruction`:

```js
const enums = aiServices.ai.enums;

try {
	const model = service.getModel(
		{
			feature: 'my-test-feature',
			capabilities: [ enums.AiCapability.TEXT_GENERATION ],
			generationConfig: {
				maxOutputTokens: 128,
				temperature: 0.2,
			},
			systemInstruction: 'You are a WordPress expert. You should respond exclusively to prompts and questions about WordPress.',
		}
	);

	// Generate text using the model.
} catch ( error ) {
	// Handle the error.
}
```

Note that not all configuration arguments are supported by every service API. However, a good number of arguments _is_ supported consistently, so here is a list of common configuration arguments that are widely supported:

* `stopSequences` _(string)_: Set of character sequences that will stop output generation.
    * Supported by all except `browser`.
* `maxOutputTokens` _(integer)_: The maximum number of tokens to include in a response candidate.
    * Supported by all except `browser`.
* `temperature` _(float)_: Floating point value to control the randomness of the output, between 0.0 and 1.0.
    * Supported by all.
* `topP` _(float)_: The maximum cumulative probability of tokens to consider when sampling.
    * Supported by all except `browser`.
* `topK` _(integer)_: The maximum number of tokens to consider when sampling.
    * Supported by all except `openai`.

Please see the [`Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Text_Generation_Config.php) for all available configuration arguments, and consult the API documentation of the respective provider to see which of them are supported.

### Expecting multimodal output

As of March 2025, [Google has introduced support for multimodal output to its first model](https://developers.googleblog.com/en/experiment-with-gemini-20-flash-native-image-generation/). This is still an early implementation, and no other service supports it yet (at the time of writing).

The AI Services plugin allows you to use this feature via the `MULTIMODAL_OUTPUT` capability. Use this capability to find a service and model that supports it. To actually enable that for the model, you need to specify which output modalities you would like to expect for your prompt, by setting the generation configuration argument `outputModalities`.

For now this only works in combination with text generation, and the only supported modalities are "text" and "image". Support will be expanded in the future as more models add support for it and eventually other combinations of modalities.

Here is a code example:

```js
const enums = aiServices.ai.enums;

try {
	const model = service.getModel(
		{
			feature: 'my-test-feature',
			capabilities: [ enums.AiCapability.TEXT_GENERATION, enums.AiCapability.MULTIMODAL_OUTPUT ],
			generationConfig: {
				outputModalities: [ 'text', 'image' ],
			},
		}
	);

	// Generate text and images using the model.
} catch ( error ) {
	// Handle the error.
}
```

### Function calling

Several AI services and their models support function calling. Using this feature, you can provide custom function definitions to the model via JSON schema. The model cannot directly invoke these functions, but it can generate structured output suggesting a specific function to call with specific arguments. You can then handle calling the corresponding function with the suggested arguments in your business logic and provide the resulting output to the AI model as part of a subsequent prompt. This powerful feature can help the AI model to gather additional context for the user prompts and better integrate it into your processes.

#### Providing function declarations

In order to allow for the model to generate function call output, you need to provide function definitions via the `tools` model parameter. Here is an example, for a hypothetical weather application where users can ask the AI model about the weather forecast:

```js
const enums = aiServices.ai.enums;

const functionDeclarations = [
	{
		name: 'get_weather',
		description: 'Returns the weather for a given location and a given timeframe.',
		parameters: {
			type: 'object',
			properties: {
				location: {
					type: 'string',
					description: 'The location to get the weather forecast for, such as a city or region.',
				},
				timeframe: {
					type: 'string',
					enum: [ 'today', 'tonight', 'tomorrow', 'next-week' ],
					description: 'The timeframe for when to get the weather forecast for.'
				},
			},
		},
	},
];

const tools = [ { functionDeclarations } ];

const content = {
	role: enums.ContentRole.USER,
	parts: [
		{
			text: 'What is the weather like today in Austin?'
		},
	]
};
try {
	const candidates = await service.generateText(
		content,
		{
			feature: 'my-test-feature',
			tools: tools,
			capabilities: [
				enums.AiCapability.FUNCTION_CALLING,
				enums.AiCapability.TEXT_GENERATION,
			],
		}
	);
} catch ( error ) {
	// Handle the error.
}
```

#### Processing a function call

Depending on the prompt, the AI model may determine that it can respond to the query without a function call, or it may determine that a function call would be useful. In other words, the response may contain either, or a mix of both text response and a function call definition.

Here is an example of how you could process such a response:

```js
let text = '';
let functionCall;
for ( const part of candidates[ 0 ].content.parts ) {
	if ( part.text ) {
		if ( text ) {
			text += '\n\n';
		}
		text += part.text;
	} else if ( part.functionCall ) {
		functionCall = {
			id: part.functionCall.id,
			name: part.functionCall.name,
			args: part.functionCall.args,
		};
	}
}

// Business logic calling the relevant function...

// The function result could have any shape, from a simple scalar value to a complex array of data.
const functionResult = {
	location: {
		city: 'Austin',
		state: 'TX',
		country: 'US',
	},
	weather: {
		summary: 'sunny',
		temperature_high: 92,
		temperature_low: 77,
	},
};
```

If `functionCall` contains data, you could then call the respective function identified by `functionCall.name` in your business logic, with the arguments specified in `functionCall.args`, which is a map of argument identifiers and their values. Both the function name and its arguments will refer to one of the functions you provided to the AI model together with the prompt.

The `functionCall.id` value is a special string identifier specific to the AI model you called. Not every AI service will provide a value for this, but if it is set, it is critical that you provide it back to the AI model with the eventual function response. In this example that response is hard coded into the `functionResult` variable.

#### Providing the function response back to the model

After calling the function, you can pass the result back to the model in a subsequent prompt to the model. Keep in mind to include the previous history from the exchange so that the model is aware of both the initial prompt, the function call it responded with and the response of the function based on your business logic.

Here is what that could look like, continuing the example from above:

```js
const enums = aiServices.ai.enums;

// This should contain the same function declarations provided before.
const functionDeclarations = [
	// ...
];

const tools = [ { functionDeclarations } ];

/*
 * This should contain both the content object with the initial user prompt, and the content object with the function
 * call received by the AI model.
 */
const contents = [
	// ...
];

// This adds the function response to the overall prompt.
const content = {
	role: enums.ContentRole.USER,
	parts: [
		{
			functionResponse: {
        id: functionCall.id,
        name: functionCall.name,
        response: functionResult,
      },
		},
	]
};
contents.push( content );

try {
	const candidates = await service.generateText(
		contents,
		{
			feature: 'my-test-feature',
			tools: tools,
			capabilities: [
				enums.AiCapability.FUNCTION_CALLING,
				enums.AiCapability.TEXT_GENERATION,
			],
		}
	);
} catch ( error ) {
	// Handle the error.
}
```

You should now get a response from the AI model that is based on the function response data you provided to it, answering the initial prompt.

## Generating image content using an AI service

The API for generating images works very similarly to the one for [generating text content](#generating-text-content-using-an-ai-service). Basically, instead of the model or service class's `generateText()` method you need to call the `generateImage()` method, and the AI capability to check for is `enums.AiCapability.IMAGE_GENERATION`.

Here is a code example to generate an image using whichever AI service and model is available and suitable for the request:

```js
const enums = aiServices.ai.enums;

const SERVICE_ARGS = { capabilities: [ enums.AiCapability.IMAGE_GENERATION ] };
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices( SERVICE_ARGS ) ) {
	const service = getAvailableService( SERVICE_ARGS );

	try {
		const candidates = await service.generateImage(
			'Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert.',
			{
				feature: 'my-test-feature',
				capabilities: [ enums.AiCapability.IMAGE_GENERATION ],
			}
		);
	} catch ( error ) {
		// Handle the error.
	}
}
```

The signature of the `generateImage()` method is almost exactly the same as the `generateText()` method. You can also provide a `Content` object as input, however please note that at the moment none of the built-in AI services support multimodal input or chat history in combination with generating images. Whenever any of the models adds support for those AI capabilities in the future, it'll work out of the box right away.

### Processing image responses

Similar to `generateText()`, the `generateImage()` model method returns an array of candidate objects - usually just one, but depending on the prompt and configuration there may be multiple alternatives.

Every candidate in the list is an object, which allows you to access its actual content as well as metadata about the particular response candidate.

For example, you can use code as follows to retrieve the generated image from the first candidate.

```js
let imageUrl = '';
for ( const part of candidates[ 0 ].content.parts ) {
	if ( part.inlineData ) {
		imageUrl = part.inlineData.data; // Data URL.
		break;
	}
	if ( part.fileData ) {
		imageUrl = part.fileData.fileUri; // Actual URL. May have limited TTL (often 1 hour).
		break;
	}
}
```

By default, image models are configured to return inline data, i.e. a data URL with base64-encoded data.

After retrieving the resulting image (data) URL, you can process it further - for example upload it to the WordPress Media Library. The AI Services plugin provides a few helper functions related to transforming different representations of a file, via [`aiServices.ai.helpers`](https://github.com/felixarntz/ai-services/tree/main/src/ai/helpers.js). For processing a data URL for a generated image, the most important helper function is `base64DataUrlToBlob()`. Here is the full list of relevant helper functions for file processing:

* `fileToBase64DataUrl( file: string, mimeType: string = '' ): string`: Returns the base64-encoded data URL representation of the given file URL.
* `fileToBlob( file: string, mimeType: string = '' ): Blob?`: Returns the binary data blob representation of the given file URL.
* `blobToBase64DataUrl( blob: Blob ): string`: Returns the base64-encoded data URL representation of the given binary data blob.
* `base64DataUrlToBlob( base64DataUrl: string ): Blob?`: Returns the binary data blob representation of the given base64-encoded data URL.

### Customizing the default image generation configuration

Similarly to how you can [customize the text generation configuration](#customizing-the-default-text-generation-configuration), you can customize the image generation configuration. The `generationConfig` key needs to contain an object with configuration arguments. These arguments are normalized in a way that works across the different AI services and their APIs.

Here is a code example using `generationConfig`:

```js
const enums = aiServices.ai.enums;

try {
	const model = service.getModel(
		{
			feature: 'my-test-feature',
			capabilities: [ enums.AiCapability.IMAGE_GENERATION ],
			generationConfig: {
				candidateCount: 4,
				aspectRatio: '16:9',
			},
		}
	);

	// Generate an image using the model.
} catch ( error ) {
	// Handle the error.
}
```

Note that not all configuration arguments are supported by every service API. Here is a list of common configuration arguments that are widely supported:

* `candidateCount` _(integer)_: Number of image candidates to generate.
* `aspectRatio` _(string)_: Aspect ratio of the generated image.

Please see the [`Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Image_Generation_Config.php) for all available configuration arguments, and consult the API documentation of the respective provider to see which of them are supported.
