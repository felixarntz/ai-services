[Back to overview](./README.md)

# Introduction to AI Services

While the main introduction to the plugin can be found in the [plugin readme](../README.md), this section provides a bit more general information about the plugin and its feature set:

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

## Technical concepts

### Sending data to AI services

In order to provide a uniform way of communicating with AI services, this plugin defines several data types that facilitate sending and receiving content of various kinds. For instance, while a simple "send a string, get a string response" may seem intuitive at first, such an approach would not allow to leverage the advanced capabilities of some AI services, such as generating images or processing multi-modal content (e.g. sending an image as well as a text prompt asking to describe the image).

This centerpiece is the "Content" data type, which has two properties:
* `role`: The role of who the content comes from (one of `user`, `model`, or `system`).
* `parts`: The array of content parts.
	* In many cases, this will be just one, but as mentioned before, more complex multi-modal prompts may require sending multiple content parts of different kinds in a single prompt.
	* Various types of parts are supported, e.g. text, inline data, or file data.

When you send an AI prompt, you don't _have_ to use this verbose format if your prompt is simple. You may alternatively send just the array of parts, or simply a string, which is sufficient for the common scenario of sending a text prompt. Under the hood, the prompt will still be parsed into the "Content" data type.

#### Examples (in JSON format)

A simple text prompt:
```json
{
	"role": "user",
	"parts": [
		{
			"text": "What can I do with WordPress?"
		}
	]
}
```

A multi-modal prompt asking to describe an image:
```json
{
	"role": "user",
	"parts": [
		{
			"text": "Please describe this image."
		},
		{
			"mimeType": "image/jpeg",
			"fileUri": "https://example.com/image.jpg"
		}
	]
}
```

### Processing AI service responses

Responses from an AI service use the same "Content" object format that is used to send prompts. While for a prompt the `role` should generally be "user", model responses use a `role` of "model".

When receiving the response from an AI model, in most cases the "Content" object will be wrapped in an array of "Candidates". This is relevant because sometimes the model may return a few alternative responses that could be chosen from. If so, the content will be found under the "content" property of the candidate.

#### Examples (in JSON format)

A simple text response from the AI model:
```json
{
	"role": "model",
	"parts": [
		{
			"text": "WordPress is the most popular content management system in the world."
		}
	]
}
```

The same text response within a set of candidates:
```json
[
	{
		"content": {
			"role": "model",
			"parts": [
				{
					"text": "WordPress is the most popular content management system in the world."
				}
			]
		},
		// Other properties of the candidate.
	},
	// Other candidates.
]
```

### PHP codebase

The PHP code is located in the `includes` directory.

The plugin's PHP codebase follows various OOP best practices, separating concerns and encapsulating different responsibilities. The public APIs to be used by other plugins are clearly defined, while most of the codebase remains internal. This helps prevent incorrect usage and potential conflicts with other plugins.

If you are interested in reviewing the PHP code in more depth, it is recommended that you focus primarily on the `Felix_Arntz\AI_Services\Services` namespace. This is where the AI infrastructure foundation is implemented.

### JavaScript codebase

The JavaScript code is located in the `src` directory. Each folder within `src` with an `index.js` file represents its own asset that is registered in the PHP codebase and can be enqueued.

The plugin's JavaScript codebase follows modern JavaScript best practices used in WordPress, using React for UI components (via [`wp.element`](https://www.npmjs.com/package/@wordpress/element)) and Redux datastores for state management (via [`wp.data`](https://www.npmjs.com/package/@wordpress/data)).

If you are interested in reviewing the JavaScript code in more depth, it is recommended that you focus primarily on the `src/ai-store` asset. This represents the datastore where the AI infrastructure foundation is implemented.
