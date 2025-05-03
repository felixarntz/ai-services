---
title: Technical Concepts of AI Services
layout: page
---

This section provides a more technical introduction about the plugin and its technical concepts.

## Technical concepts

### Sending data to AI services

In order to provide a uniform way of communicating with AI services, this plugin defines several data types that facilitate sending and receiving content of various kinds. For instance, while a simple "send a string, get a string response" may seem intuitive at first, such an approach would not allow to leverage the advanced capabilities of some AI services, such as generating images or processing multi-modal content (e.g. sending an image as well as a text prompt asking to describe the image).

This centerpiece is the "Content" data type, which has two properties:

* `role`: The role of who the content comes from (one of `user`, `model`, or `system`).
* `parts`: The array of content parts.
    * In many cases, this will be just one, but as mentioned before, more complex multi-modal prompts may require sending multiple content parts of different kinds in a single prompt.
    * Various types of parts are supported, e.g. text, inline data, or file data.

When you send an AI prompt, you don't _have_ to use this verbose format if your prompt is simple. You may alternatively send just the array of parts, or simply a string, which is sufficient for the common scenario of sending a text prompt. Under the hood, the prompt will still be parsed into the "Content" data type.

#### Prompt examples (in JSON format)

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

#### Response examples (in JSON format)

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

If you are interested in reviewing the JavaScript code in more depth, it is recommended that you focus primarily on the `src/ai` asset. This represents the API and datastore where the AI infrastructure foundation is implemented.
