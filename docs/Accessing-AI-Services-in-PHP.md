---
title: Accessing AI Services in PHP
layout: page
---

This section provides some documentation on how to access AI services in PHP. This is relevant for any plugins that would like to generate content via server-side logic.

The canonical entry point to all of the PHP public APIs is the `ai_services()` function in the global namespace, which returns the canonical instance of the [`Felix_Arntz\AI_Services\Services\Services_API` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/Services_API.php). The concrete usage is best outlined by examples. For illustrative purposes, here is a full example of generating text content using the `google` service:

```php
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$candidates = $service
			->get_model(
				array(
					'feature'      => 'my-test-feature',
					'capabilities' => array( \Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability::TEXT_GENERATION ),
				)
			)
			->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
```

For more specific examples with explanations, see the following sections.

## Retrieving an available AI service

To use any AI capabilities, you first have to retrieve an available AI service. Which AI services are available depends on the end user, e.g. the site owner. They are free to configure any of the registered AI services with credentials. While many sites will likely only have one AI service configured, some sites may have all services configured (e.g. if the site owner has subscriptions with all of them or if they are free to use).

### Using a specific AI service

You can pass the slug of a specific AI service to the `Services_API::get_available_service()` method. Before doing so, it is recommended to first check whether the service available (i.e. configured by the user with valid credentials), using the `Services_API::is_service_available()` method:

```php
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	// Do something with the AI service.
}
```

Alternatively, if you don't want to use the `Services_API::is_service_available()` method, you need to wrap the `Services_API::get_available_service()` in a try catch statement, as it will throw an exception if you try to access a service that is not available:

```php
try {
	$service = ai_services()->get_available_service( 'google' );
} catch ( InvalidArgumentException $e ) {
	// Handle the exception.
}
// Do something with the AI service.
```

### Using any available AI service

For many AI use-cases, relying on different AI services may be feasible. For example, to respond to a simple text prompt, you could use _any_ AI service that supports text generation. If so, it is advised to not require usage of a _specific_ AI service, so that the end user can configure whichever service they prefer and still use the relevant AI functionality you're implementing in your plugin. You can do so by passing arguments to the `Services_API::has_available_services()` and `Services_API::get_available_service()` methods for which capabilities you require for what you intend to do (for example to generate text):

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

$required_capabilities = array( 'capabilities' => array( AI_Capability::TEXT_GENERATION ) );
if ( ai_services()->has_available_services( $required_capabilities ) ) {
	$service = ai_services()->get_available_service( $required_capabilities );
	// Do something with the AI service.
}
```

Alternatively, if you don't want to use the `Services_API::has_available_services()` method, you need to wrap the `Services_API::get_available_service()` in a try catch statement, as it will throw an exception if you try to access a service that is not available:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

try {
	$service = ai_services()->get_available_service( array( 'capabilities' => array( AI_Capability::TEXT_GENERATION ) ) );
} catch ( InvalidArgumentException $e ) {
	// Handle the exception.
}
// Do something with the AI service.
```

In some instances, you may have a preference for a few specific AI services that work well for your AI use-case - in those cases you can pass multiple slugs, to retrieve whichever one the user has configured:

```php
if ( ai_services()->has_available_services( array( 'slugs' => array( 'google', 'openai' ) ) ) ) {
	$service = ai_services()->get_available_service( array( 'slugs' => array( 'google', 'openai' ) ) );
	// Do something with the AI service.
}
```

## Generating text content using an AI service

Once you have retrieved an AI service, you can use it to get a text response to a prompt. You need to use the `get_model()` method of the service to get an instance of the model (at a minimum passing a unique "feature" identifier, and optionally custom configuration), and afterwards call the `generate_text()` method of the model. This method will only be available if the service implements the "text_generation" capability - which all built-in services do, but there may be further custom AI services registered some of which may only support other capabilities such as "image_generation".

The recommended way to refer to any AI capabilities is by using the available constants on the `Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability` class.

### Using the preferred model for certain capabilities

Here is an example of how to generate the response to a simple prompt, using the preferred model for text generation. Assume that the `$service` variable contains an available service instance that supports `AI_Capability::TEXT_GENERATION` (e.g. based on the previous examples):

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'capabilities' => array( AI_Capability::TEXT_GENERATION ),
			)
		)
		->generate_text( 'What can I do with WordPress?' );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

### Using a custom model

You can also select a specific model from a service. Of course the available models differ per service, so if you intend to do use a custom model, you will need to handle this per service. If the service instance used may be from different service, you can use the `get_service_slug()` method of the service to determine its slug. In this example, let's assume the `$service` variable contains one of the `google` or `openai` services:

```php
if( $service->get_service_slug() === 'openai' ) {
	$model = 'gpt-4o';
} else {
	$model = 'gemini-1.5-pro';
}
try {
	$candidates = $service
		->get_model(
			array(
				'feature' => 'my-test-feature',
				'model'   => $model,
			)
		)
		->generate_text( 'What can I do with WordPress?' );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

Note: Alongside the model key in the array, you may pass other configuration arguments supported by models of the respective service.

### Sending multimodal prompts

As mentioned in the [technical concepts section about sending data to AI services](./Technical-Concepts-of-AI-Services.md#sending-data-to-AI-services), passing a string to the `generate_text()` method is effectively just a shorthand syntax for the more elaborate content format. To pass more elaborate content as a prompt, you can use instances of the [`Felix_Arntz\AI_Services\Services\API\Types\Content` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Content.php) or the [`Felix_Arntz\AI_Services\Services\API\Types\Parts` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Parts.php). For example, if the AI service supports multimodal content, you can ask it to describe a provided image:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;

$parts = new Parts();
$parts->add_text_part( 'Briefly describe what is displayed in the following image using a single sentence.' );
$parts->add_file_data_part( 'image/jpeg', 'https://example.com/image.jpg' );
$content = new Content( Content_Role::USER, $parts );
try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'capabilities' => array(
					AI_Capability::MULTIMODAL_INPUT,
					AI_Capability::TEXT_GENERATION,
				),
			)
		)
		->generate_text( $content );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

You can also pass an array of content objects. In this case, this will be interpreted as the history including previous message exchanges from the same chat.

### Processing text responses

The `generate_text()` model method returns an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidates` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Candidates.php) which is an iterable object that contains the alternative response candidates - usually just one, but depending on the prompt and configuration there may be multiple alternatives.

Every candidate in the list is an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidate` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Candidate.php), which allows you to access its actual content as well as metadata about the particular response candidate.

For example, you can use code as follows to retrieve the text content of the first candidate.

```php
$text = '';
foreach ( $candidates->get( 0 )->get_content()->get_parts() as $part ) {
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part ) {
		if ( $text !== '' ) {
			$text .= "\n\n";
		}
		$text .= $part->get_text();
	}
}
```

This code example realistically should work in 99% of use-cases. However, there may be a scenario where the first candidate only contains non-text content. In that case the code example above would result in an empty string. Therefore, technically speaking it is the safest approach to first find a candidate that has any text content.

As this can be tedious, the AI Services API provides a class with static helper methods to make it extremely simple. You can access the helper methods via the [`Felix_Arntz\AI_Services\Services\API\Helpers` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Helpers.php).

The following example shows how you can accomplish the above in a safer, yet simpler way:

```php
use Felix_Arntz\AI_Services\Services\API\Helpers;

$text = Helpers::get_text_from_contents(
	Helpers::get_candidate_contents( $candidates )
);
```

### Streaming text responses

Alternatively to using the `generate_text()` method, you can use the `stream_generate_text()` method so that the response is streamed. This can help provide more immediate feedback to the user, since chunks with partial response candidates will be available iteratively while the model still processes the remainder of the response. In other words, you can print the text from these chunks right away, so that it almost looks as if the generative AI model was typing it. Especially for prompts that expect a larger response (e.g. more than one paragraph), streaming the response can have major benefits on user experience.

The `stream_generate_text()` method takes the same parameters as the `generate_text()` method. Instead of returning the final candidates instance though, it returns a generator that yields the partial candidates chunks. This generator can be used to iterate over the chunks as they arrive.

The following example shows how you could use streaming:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Helpers;

try {
	$candidates_generator = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'capabilities' => array( AI_Capability::TEXT_GENERATION ),
			)
		)
		->stream_generate_text( 'What can I do with WordPress?' );

	foreach ( $candidates_generator as $candidates ) {
		$text = Helpers::get_text_from_contents(
			Helpers::get_candidate_contents( $candidates )
		);

		echo $text;
	}
} catch ( Exception $e ) {
	// Handle the exception.
}
```

It's worth noting that streaming is likely more useful in JavaScript than in PHP, since in PHP there are typically no opportunities to print the iterative responses to the user as they come in. That said, streaming can certainly have value in PHP as well: It is for example used in the plugin's WP-CLI command.

### Customizing the default text generation configuration

When retrieving a model using the `get_model()` method, it is possible to provide a `generationConfig` argument to customize the model configuration. For text generation, the `generationConfig` key needs to contain an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Text_Generation_Config.php), which allows to provide various model configuration arguments in a normalized way that works across the different AI services and their APIs.

Additionally to `generationConfig`, you can pass a `systemInstruction` argument if you want to provide a custom instruction for how the model should behave. By setting a system instruction, you give the model additional context to understand its tasks, provide more customized responses, and adhere to specific guidelines over the full user interaction with the model.

Here is a code example using both `generationConfig` and `systemInstruction`:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;

try {
	$model = $service
		->get_model(
			array(
				'feature'           => 'my-test-feature',
				'capabilities'      => array( AI_Capability::TEXT_GENERATION ),
				'generationConfig'  => Text_Generation_Config::from_array(
					array(
						'maxOutputTokens' => 128,
						'temperature'     => 0.2,
					)
				),
				'systemInstruction' => 'You are a WordPress expert. You should respond exclusively to prompts and questions about WordPress.',
			)
		);

	// Generate text using the model.
} catch ( Exception $e ) {
	// Handle the exception.
}
```

Note that not all configuration arguments are supported by every service API. However, a good number of arguments _is_ supported consistently, so here is a list of common configuration arguments that are widely supported:

* `stopSequences` _(string)_: Set of character sequences that will stop output generation.
    * Supported by all.
* `maxOutputTokens` _(integer)_: The maximum number of tokens to include in a response candidate.
    * Supported by all.
* `temperature` _(float)_: Floating point value to control the randomness of the output, between 0.0 and 1.0.
    * Supported by all.
* `topP` _(float)_: The maximum cumulative probability of tokens to consider when sampling.
    * Supported by all.
* `topK` _(integer)_: The maximum number of tokens to consider when sampling.
    * Supported by all except `openai`.

Please see the [`Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Text_Generation_Config.php) for all available configuration arguments, and consult the API documentation of the respective provider to see which of them are supported.

### Expecting multimodal output

As of March 2025, [Google has introduced support for multimodal output to its first model](https://developers.googleblog.com/en/experiment-with-gemini-20-flash-native-image-generation/). This is still an early implementation, and no other service supports it yet (at the time of writing).

The AI Services plugin allows you to use this feature via the `MULTIMODAL_OUTPUT` capability. Use this capability to find a service and model that supports it. To actually enable that for the model, you need to specify which output modalities you would like to expect for your prompt, by setting the generation configuration argument `outputModalities`.

For now this only works in combination with text generation, and the only supported modalities are "text" and "image". Support will be expanded in the future as more models add support for it and eventually other combinations of modalities.

Here is a code example:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Enums\Modality;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;

try {
	$model = $service
		->get_model(
			array(
				'feature'           => 'my-test-feature',
				'capabilities'      => array( AI_Capability::TEXT_GENERATION, AI_Capability::MULTIMODAL_OUTPUT ),
				'generationConfig'  => Text_Generation_Config::from_array(
					array(
						'outputModalities' => array( Modality::TEXT, Modality::IMAGE ),
					)
				),
			)
		);

	// Generate text and images using the model.
} catch ( Exception $e ) {
	// Handle the exception.
}
```

### Function calling

Several AI services and their models support function calling. Using this feature, you can provide custom function definitions to the model via JSON schema. The model cannot directly invoke these functions, but it can generate structured output suggesting a specific function to call with specific arguments. You can then handle calling the corresponding function with the suggested arguments in your business logic and provide the resulting output to the AI model as part of a subsequent prompt. This powerful feature can help the AI model to gather additional context for the user prompts and better integrate it into your processes.

#### Providing function declarations

In order to allow for the model to generate function call output, you need to provide function definitions via the `tools` model parameter. Here is an example, for a hypothetical weather application where users can ask the AI model about the weather forecast:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;

$function_declarations = array(
	array(
		'name'        => 'get_weather',
		'description' => 'Returns the weather for a given location and a given timeframe.',
		'parameters'  => array(
			'type'       => 'object',
			'properties' => array(
				'location'  => array(
					'type'        => 'string',
					'description' => 'The location to get the weather forecast for, such as a city or region.',
				),
				'timeframe' => array(
					'type'        => 'string',
					'enum'        => array( 'today', 'tonight', 'tomorrow', 'next-week' ),
					'description' => 'The timeframe for when to get the weather forecast for.'
				),
			),
		),
	),
);

$tools = new Tools();
$tools->add_function_declarations_tool( $function_declarations );
$parts = new Parts();
$parts->add_text_part( 'What is the weather like today in Austin?' );
$content = new Content( Content_Role::USER, $parts );
try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'tools'        => $tools,
				'capabilities' => array(
					AI_Capability::FUNCTION_CALLING,
					AI_Capability::TEXT_GENERATION,
				),
			)
		)
		->generate_text( $content );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

#### Processing a function call

Depending on the prompt, the AI model may determine that it can respond to the query without a function call, or it may determine that a function call would be useful. In other words, the response may contain either, or a mix of both text response and a function call definition.

Here is an example of how you could process such a response:

```php
$text          = '';
$function_call = null;
foreach ( $candidates->get( 0 )->get_content()->get_parts() as $part ) {
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part ) {
		if ( $text !== '' ) {
			$text .= "\n\n";
		}
		$text .= $part->get_text();
	} elseif ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part ) {
		$function_call = array(
			'id'   => $part->get_id(),
			'name' => $part->get_name(),
			'args' => $part->get_args(),
		);
	}
}

// Business logic calling the relevant function...

// The function result could have any shape, from a simple scalar value to a complex array of data.
$function_result = array(
	'location' => array(
		'city'    => 'Austin',
		'state'   => 'TX',
		'country' => 'US',
	),
	'weather'  => array(
		'summary'          => 'sunny',
		'temperature_high' => 92,
		'temperature_low'  => 77,
	),
);
```

If `$function_call` contains data, you could then call the respective function identified by `$function_call['name']` in your business logic, with the arguments specified in `$function_call['args']`, which is a map of argument identifiers and their values. Both the function name and its arguments will refer to one of the functions you provided to the AI model together with the prompt.

The `$function_call['id']` value is a special string identifier specific to the AI model you called. Not every AI service will provide a value for this, but if it is set, it is critical that you provide it back to the AI model with the eventual function response. In this example that response is hard coded into the `$function_result` variable.

#### Providing the function response back to the model

After calling the function, you can pass the result back to the model in a subsequent prompt to the model. Keep in mind to include the previous history from the exchange so that the model is aware of both the initial prompt, the function call it responded with and the response of the function based on your business logic.

Here is what that could look like, continuing the example from above:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;

// This should contain the same function declarations provided before.
$function_declarations = array(
	// ...
);

$tools = new Tools();
$tools->add_function_declarations_tool( $function_declarations );

/*
 * This should contain both the content object with the initial user prompt, and the content object with the function
 * call received by the AI model.
 */
$contents = array(
	// ...
);

// This adds the function response to the overall prompt.
$parts = new Parts();
$parts->add_function_response_part( $function_call['id'], $function_call['name'], $function_result );
$content    = new Content( Content_Role::USER, $parts );
$contents[] = $content;

try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'tools'        => $tools,
				'capabilities' => array(
					AI_Capability::FUNCTION_CALLING,
					AI_Capability::TEXT_GENERATION,
				),
			)
		)
		->generate_text( $contents );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

You should now get a response from the AI model that is based on the function response data you provided to it, answering the initial prompt.

## Generating image content using an AI service

The API for generating images works very similarly to the one for [generating text content](#generating-text-content-using-an-ai-service). Basically, instead of the model class's `generate_text()` method you need to call the `generate_image()` method, and the AI capability to check for is `AI_Capability::IMAGE_GENERATION`.

Here is a code example to generate an image using whichever AI service and model is available and suitable for the request:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

try {
	$service = ai_services()->get_available_service( array( 'capabilities' => array( AI_Capability::IMAGE_GENERATION ) ) );
} catch ( InvalidArgumentException $e ) {
	// Handle the exception.
}

try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'capabilities' => array( AI_Capability::IMAGE_GENERATION ),
			)
		)
		->generate_image( 'Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert.' );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

The signature of the `generate_image()` method is almost exactly the same as the `generate_text()` method. You can also provide a `Content` object as input, however please note that at the moment none of the built-in AI services support multimodal input or chat history in combination with generating images. Whenever any of the models adds support for those AI capabilities in the future, it'll work out of the box right away.

### Processing image responses

Similar to `generate_text()`, the `generate_image()` model method returns an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidates` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Candidates.php) which is an iterable object that contains the alternative response candidates - usually just one, but depending on the prompt and configuration there may be multiple alternatives.

Every candidate in the list is an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidate` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Candidate.php), which allows you to access its actual content as well as metadata about the particular response candidate.

For example, you can use code as follows to retrieve the generated image from the first candidate.

```php
$image_url = '';
foreach ( $candidates->get( 0 )->get_content()->get_parts() as $part ) {
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part ) {
		$image_url = $part->get_base64_data(); // Data URL.
		break;
	}
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part ) {
		$image_url = $part->get_file_uri(); // Actual URL. May have limited TTL (often 1 hour).
		break;
	}
}
```

By default, image models are configured to return inline data, i.e. a data URL with base64-encoded data.

After retrieving the resulting image (data) URL, you can process it further - for example upload it to the WordPress Media Library. The AI Services plugin provides a few helper methods related to transforming different representations of a file, via the [`Felix_Arntz\AI_Services\Services\API\Helpers` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Helpers.php). Related to these is the [`Felix_Arntz\AI_Services\Services\API\Types\Blob` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Blob.php), which represents a binary data blob and is inspired by the [native JavaScript `Blob` class](https://developer.mozilla.org/en-US/docs/Web/API/Blob). For processing a data URL for a generated image, the most important helper method is `Helpers::base64_data_url_to_blob()`. Here is the full list of relevant helper methods for file processing:

* `Helpers::file_to_base64_data_url( string $file, string $mime_type ): string`: Returns the base64-encoded data URL representation of the given file URL.
* `Helpers::file_to_blob( string $file, string $mime_type = '' ): ?Blob`: Returns the binary data blob representation of the given file URL.
* `Helpers::blob_to_base64_data_url( Blob $blob ): string`: Returns the base64-encoded data URL representation of the given binary data blob.
* `Helpers::base64_data_url_to_blob( string $base64_data_url ): ?Blob`: Returns the binary data blob representation of the given base64-encoded data URL.

### Customizing the default image generation configuration

Similarly to how you can [customize the text generation configuration](#customizing-the-default-text-generation-configuration), you can customize the image generation configuration. For image generation, the `generationConfig` key needs to contain an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Image_Generation_Config.php), which allows to provide various model configuration arguments in a normalized way that works across the different AI services and their APIs.

Here is a code example using `generationConfig`:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;

try {
	$model = $service
		->get_model(
			array(
				'feature'          => 'my-test-feature',
				'capabilities'     => array( AI_Capability::IMAGE_GENERATION ),
				'generationConfig' => Image_Generation_Config::from_array(
					array(
						'candidateCount' => 4,
						'aspectRatio'    => '16:9',
					)
				),
			)
		);

	// Generate an image using the model.
} catch ( Exception $e ) {
	// Handle the exception.
}
```

Note that not all configuration arguments are supported by every service API. Here is a list of common configuration arguments that are widely supported:

* `candidateCount` _(integer)_: Number of image candidates to generate.
* `aspectRatio` _(string)_: Aspect ratio of the generated image.

Please see the [`Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Image_Generation_Config.php) for all available configuration arguments, and consult the API documentation of the respective provider to see which of them are supported.

## Transforming text to speech using an AI service

The API for transforming text to speech works very similarly to the one for [generating text content](#generating-text-content-using-an-ai-service). Basically, instead of the model class's `generate_text()` method you need to call the `text_to_speech()` method, and the AI capability to check for is `AI_Capability::TEXT_TO_SPEECH`.

Here is a code example to transform text to speech using whichever AI service and model is available and suitable for the request:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

try {
	$service = ai_services()->get_available_service( array( 'capabilities' => array( AI_Capability::TEXT_TO_SPEECH ) ) );
} catch ( InvalidArgumentException $e ) {
	// Handle the exception.
}

try {
	$candidates = $service
		->get_model(
			array(
				'feature'      => 'my-test-feature',
				'capabilities' => array( AI_Capability::TEXT_TO_SPEECH ),
			)
		)
		->text_to_speech( 'What a beautiful day it is today!' );
} catch ( Exception $e ) {
	// Handle the exception.
}
```

The signature of the `text_to_speech()` method is almost exactly the same as the `generate_text()` method. You can also provide a `Content` object as input, however please note that at the moment none of the built-in AI services support multimodal input or chat history in combination with transforming text to speech. Whenever any of the models adds support for those AI capabilities in the future, it'll work out of the box right away.

### Processing audio responses

Similar to `generate_text()`, the `text_to_speech()` model method returns an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidates` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Candidates.php) which is an iterable object that contains the alternative response candidates - today it's just one, but in the future returning multiple alternatives could be supported once provider models allow for it.

Every candidate in the list is an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Candidate` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types\Candidate.php), which allows you to access its actual content as well as metadata about the particular response candidate.

For example, you can use code as follows to retrieve the generated audio from the first candidate.

```php
$speech_url = '';
foreach ( $candidates->get( 0 )->get_content()->get_parts() as $part ) {
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part ) {
		$speech_url = $part->get_base64_data(); // Data URL.
		break;
	}
	if ( $part instanceof \Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part ) {
		$speech_url = $part->get_file_uri(); // Actual URL. May have limited TTL (often 1 hour).
		break;
	}
}
```

By default, text-to-speech models are configured to return inline data, i.e. a data URL with base64-encoded data.

After retrieving the resulting audio (data) URL, you can process it further - for example save it to a file or stream it to the user. The AI Services plugin provides a few helper methods related to transforming different representations of a file, via the [`Felix_Arntz\AI_Services\Services\API\Helpers` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Helpers.php). Related to these is the [`Felix_Arntz\AI_Services\Services\API\Types\Blob` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Blob.php), which represents a binary data blob and is inspired by the [native JavaScript `Blob` class](https://developer.mozilla.org/en-US/docs/Web/API/Blob). For processing a data URL for generated audio, the most important helper method is `Helpers::base64_data_url_to_blob()`. Here is the full list of relevant helper methods for file processing:

* `Helpers::file_to_base64_data_url( string $file, string $mime_type ): string`: Returns the base64-encoded data URL representation of the given file URL.
* `Helpers::file_to_blob( string $file, string $mime_type = '' ): ?Blob`: Returns the binary data blob representation of the given file URL.
* `Helpers::blob_to_base64_data_url( Blob $blob ): string`: Returns the base64-encoded data URL representation of the given binary data blob.
* `Helpers::base64_data_url_to_blob( string $base64_data_url ): ?Blob`: Returns the binary data blob representation of the given base64-encoded data URL.

### Customizing the default text-to-speech configuration

Similarly to how you can [customize the text generation configuration](#customizing-the-default-text-generation-configuration), you can customize the text-to-speech configuration. For text-to-speech, the `generationConfig` key needs to contain an instance of the [`Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Text_To_Speech_Generation_Config.php), which allows to provide various model configuration arguments in a normalized way that works across the different AI services and their APIs.

Here is a code example using `generationConfig`:

```php
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Generation_Config;

try {
	$model = $service
		->get_model(
			array(
				'feature'          => 'my-test-feature',
				'capabilities'     => array( AI_Capability::TEXT_TO_SPEECH ),
				'generationConfig' => Text_To_Speech_Generation_Config::from_array(
					array(
						'voice'            => 'coral',
						'responseMimeType' => 'audio/wav',
					)
				),
			)
		);

	// Transform text to speech using the model.
} catch ( Exception $e ) {
	// Handle the exception.
}
```

Note that not all configuration arguments are supported by every service API. Here is a list of common configuration arguments that are widely supported:

* `voice` _(string)_: The voice to use for the audio.
* `responseMimeType` _(string)_: The format of the audio byte stream.

Please see the [`Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Generation_Config` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Types/Text_To_Speech_Generation_Config.php) for all available configuration arguments, and consult the API documentation of the respective provider to see which of them are supported.
