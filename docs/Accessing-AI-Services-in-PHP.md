[Back to overview](./README.md)

# Accessing AI Services in PHP

This section provides some documentation on how to access AI services in PHP. This is relevant for any plugins that would like to generate content via server-side logic.

The canonical entry point to all of the PHP public APIs is the `ai_services()` function in the global namespace, which returns the canonical instance of the [`Felix_Arntz\AI_Services\Services\Services_API` class](../includes/Services/Services_API.php). The concrete usage is best outlined by examples. For illustrative purposes, here is a full example of generating text content using the `google` service:

```php
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
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
if ( ai_services()->has_available_services( array( 'capabilities' => array( 'text_generation' ) ) ) ) {
	$service = ai_services()->get_available_service( array( 'capabilities' => array( 'text_generation' ) ) );
	// Do something with the AI service.
}
```

Alternatively, if you don't want to use the `Services_API::has_available_services()` method, you need to wrap the `Services_API::get_available_service()` in a try catch statement, as it will throw an exception if you try to access a service that is not available:

```php
try {
  $service = ai_services()->get_available_service( array( 'capabilities' => array( 'text_generation' ) ) );
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

Once you have retrieved an AI service, you can use it to get a text response to a prompt. You need to use the `get_model()` method of the service to get an instance of the model (optionally with custom configuration), and afterwards call the `generate_text()` method of the model. This method will only be available if the service implements the "text_generation" capability - which all built-in services do, but there may be further custom AI services registered some of which may only support other capabilities such as "image_generation".

### Using the default model

Here is an example of how to generate the response to a simple prompt, using the default model. Assume that the `$service` variable contains an available service instance that supports "text_generation" (e.g. based on the previous examples):

```php
try {
  $result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
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
  $result = $service->get_model( array( 'model' => $model ) )->generate_text( 'What can I do with WordPress?' );
} catch ( Exception $e ) {
  // Handle the exception.
}
```

Note: Alongside the model key in the array, you may pass other configuration arguments supported by models of the respective service.

### Sending multimodal prompts

As mentioned in the [introduction section about sending data to AI services](./Introduction-to-AI-Services.md#sending-data-to-AI-services-and-processing-their-responses), passing a string to the `generate_text()` method is effectively just a shorthand syntax for the more elaborate content format. To pass more elaborate content as a prompt, you can use instances of the [`Felix_Arntz\AI_Services\Services\Types\Content` class](../includes/Services/Types/Content.php) or the [`Felix_Arntz\AI_Services\Services\Types\Parts` class](../includes/Services/Types/Parts.php). For example, if the AI service supports multimodal content, you can ask it to describe a provided image:

```php
$parts = new \Felix_Arntz\AI_Services\Services\Types\Parts();
$parts->add_text_part( 'Briefly describe what is displayed in the following image using a single sentence.' );
$parts->add_file_data_part( 'image/jpeg', 'https://example.com/image.jpg' );
$content = new \Felix_Arntz\AI_Services\Services\Types\Content( 'user', $parts );
try {
  $result = $service->get_model()->generate_text( $content );
} catch ( Exception $e ) {
  // Handle the exception.
}
```

You can also pass an array of content objects. In this case, this will be interpreted as the history including previous message exchanges from the same chat.

## Generating image content using an AI service

Coming soon.
