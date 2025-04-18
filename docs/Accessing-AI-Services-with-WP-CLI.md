---
title: Accessing AI Services with WP-CLI
layout: page
---

This section provides some documentation on how to access AI services through the command line with WP-CLI.

The canonical entry point to all of the AI Services commands is the `wp ai-services` command namespace. The concrete usage is best outlined by examples. For illustrative purposes, here is a full example of generating text content using the `google` service:

```bash
wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature
```

For any command, you can optionally provide the global WP-CLI argument `--user`, to run the command using a specific account on your site.

For more specific examples with explanations, see the following sections.

## Available commands

The following WP-CLI commands are available:

* `wp ai-services list`: Lists the registered AI services.
* `wp ai-services get`: Gets details about a registered AI service.
* `wp ai-services list-models`: Lists the models for a registered and available AI service.
* `wp ai-services generate-text`: Generates text content using a generative model from an available AI service.
* `wp ai-services generate-image`: Generates an image using a generative model from an available AI service.

Use `wp help ai-services` or `wp help ai-services <command>` to get detailed usage instructions and lists of the available arguments for each command.

## Generating text content using an AI service

The following examples cover the `wp ai-services generate-text` command.

### Using a specific AI service for text generation

You can provide the slug of a specific AI service as first positional argument to the `wp ai-services generate-text` command, for example the `google` service:

```bash
wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature
```

Note that this command will return an error if the service is not available (i.e. configured by the user with valid credentials). Therefore it is recommended to first check whether the service available, for example using the `wp ai-services get` command:

```bash
if [ "$(wp ai-services get google --field=is_available)" == "true" ]; then
  wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature
else
  echo "The google service is not available."
fi
```

### Using a specific AI model for text generation

If you want to go more granular and also specify which exact model to use from the service, you can specify the model slug after the service slug in the `wp ai-services generate-text` command. The following example specifies to use the `gemini-1.5-pro` model from the `google` service:

```bash
wp ai-services generate-text google gemini-1.5-pro "What can I do with WordPress?" --feature=my-test-feature
```

### Using any available AI service for text generation

For many AI use-cases, relying on different AI services may be feasible. For example, to respond to a simple text prompt, you could use _any_ AI service that supports text generation. If so, it is advised to not require usage of a _specific_ AI service, so that the end user can configure whichever service they prefer and still use the relevant command. You can do so by simply omitting both the service and model positional arguments from the `wp ai-services generate-text` command:

```bash
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature
```

This command will automatically choose whichever service and model with text generation capabilities is available. It will only return an error if no capable service is configured at all.

### Sending multimodal prompts

Additionally to a simple text prompt, you can provide multimodal input to the model by referencing a WordPress media file (also called "attachment"). For example, referencing an image file allows the model to return a response related to what is shown in the image. In order to use a WordPress attachment as multimodal input, you need to use the optional `--attachment` argument. Here is an example:

```bash
wp ai-services generate-text "Generate alternative text for this image." --feature=alt-text-generator --attachment-id=123
```

### Streaming text responses

The `wp ai-services generate-text` command streams text responses by default. This can help provide more immediate feedback to the user, since chunks with partial response candidates will be available iteratively while the model still processes the remainder of the response.

An exception where it does not stream the response, but returns it all at once is if any function declarations are present.

If you prefer to show the complete text response in one go instead, you can disable streaming in WP-CLI by using the `ai_services_wp_cli_use_streaming` filter:

```php
add_filter( 'ai_services_wp_cli_use_streaming', '__return_false' );
```

### Function calling

Several AI services and their models support function calling. Using this feature, you can provide custom function definitions to the model via JSON schema. The model cannot directly invoke these functions, but it can generate structured output suggesting a specific function to call with specific arguments. You can then handle calling the corresponding function with the suggested arguments in your business logic and provide the resulting output to the AI model as part of a subsequent prompt. This powerful feature can help the AI model to gather additional context for the user prompts and better integrate it into your processes.

You can provide function declarations alongside a WP-CLI prompt by using the optional `--function-declarations` argument. It must contain a JSON-encoded array of function declarations to pass to the model as tools. Here is an example:

```bash
local function_declarations='[
  {
    "name": "get_weather",
    "description": "Returns the weather for today for a given location.",
    "parameters": {
      "type": "object",
      "properties": {
        "location": {
          "type":"string",
          "description": "The location to get the weather for, such as a city or region."
        }
      }
    }
  }
]'
wp ai-services generate-text "What is the weather today in Austin?" --feature=weather-info --function-declarations="$function_declarations"
```

## Generating image content using an AI service

The following examples cover the `wp ai-services generate-image` command.

### Using a specific AI service for image generation

You can provide the slug of a specific AI service as first positional argument to the `wp ai-services generate-image` command, for example the `google` service:

```bash
wp ai-services generate-image google "Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert." --feature=my-test-feature
```

Note that this command will return an error if the service is not available (i.e. configured by the user with valid credentials). Therefore it is recommended to first check whether the service available, for example using the `wp ai-services get` command:

```bash
if [ "$(wp ai-services get google --field=is_available)" == "true" ]; then
  wp ai-services generate-image google "Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert." --feature=my-test-feature
else
  echo "The google service is not available."
fi
```

### Using a specific AI model for image generation

If you want to go more granular and also specify which exact model to use from the service, you can specify the model slug after the service slug in the `wp ai-services generate-image` command. The following example specifies to use the `dall-e-2` model from the `openai` service:

```bash
wp ai-services generate-image openai dall-e-2 "Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert." --feature=my-test-feature
```

### Using any available AI service for image generation

If it is feasible for your use-case to rely on different AI services, it is advised to not require usage of a _specific_ AI service, so that the end user can configure whichever service they prefer and still use the relevant command. You can do so by simply omitting both the service and model positional arguments from the `wp ai-services generate-image` command:

```bash
wp ai-services generate-image "Photorealistic image with an aerial shot of a Cavalier King Charles Spaniel tanning himself at an oasis in a desert." --feature=my-test-feature
```

This command will automatically choose whichever service and model with image generation capabilities is available. It will only return an error if no capable service is configured at all.
