[Back to overview](./README.md)

# Customizing AI Services Model Parameters

You can use the `ai_services_model_params` filter in PHP to customize the model parameters before they are used to retrieve a given AI service model.

This filter is run consistently in any context, regardless of whether the AI model is used via PHP, JavaScript, or WP-CLI.

This can be helpful, for example, if you need to inject custom model configuration parameters or a custom system instruction for a specific feature in a way that it happens dynamically on the server.

Here is an example code snippet which injects a custom system instruction whenever the feature `my-movie-expert` is used:

```php
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
```

Note that this filter does not allow you to change the `feature` parameter, as that needs to be controlled by the caller.
