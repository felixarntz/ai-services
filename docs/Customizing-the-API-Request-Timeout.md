---
title: Customizing the API Request Timeout
layout: page
---

By default, the AI Services plugin uses a timeout of 30 seconds for API requests. Provider-specific implementations may sometimes override this default, for example using a higher value for heavier tasks such as image generation.

To modify the API request timeout for a specific process, you can include a `timeout` key in the optional `$request_options` parameter, either when retrieving the model object, or for the specific model method invocation:

```php
$service = ai_services()->get_available_service( $service_args );

// This sets the timeout for all interactions using this `$model` instance to 15 seconds.
$model = $service->get_model( $model_params, array( 'timeout' => 15 ) );

// This sets the timeout for only this one call to 60 seconds.
$candidates = $model->generate_text( $content, array( 'timeout' => 60 ) );
```

Alternatively to these specific adjustments, you can override the API request timeout centrally for any usage of the AI Services plugin via the `ai_services_request_timeout` filter. This filter is run for every single AI API request that AI Services makes.

Here is an example, consistently setting the timeout to 60 seconds for every AI API request:

```php
add_filter(
	'ai_services_request_timeout',
	function () {
		return 60;
	}
);
```
