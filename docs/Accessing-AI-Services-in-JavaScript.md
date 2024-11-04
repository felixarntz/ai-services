[Back to overview](./README.md)

# Accessing AI Services in JavaScript

This section provides some documentation on how to access AI services in JavaScript. This is relevant for any plugins that would like to generate content via client-side logic.

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

For more specific examples with explanations, see the following sections.

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

As mentioned in the [introduction section about sending data to AI services](./Introduction-to-AI-Services.md#sending-data-to-AI-services), passing a string to the `generateText()` method is effectively just a shorthand syntax for the more elaborate content format. To pass more elaborate content as a prompt, you can use content objects or part arrays. For example, if the AI service supports multimodal content, you can ask it to describe a provided image:

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
      feature: 'my-test-feature'
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

### Processing responses

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

As this can be tedious, the AI Services API provides a set of helper methods to make it extremely simple. You can access the helper methods via `aiServices.ai.helpers` from the `aiServices` JavaScript global.

The following example shows how you can accomplish the above in a safer, yet simpler way:
```js
const helpers = aiServices.ai.helpers;
const text = helpers.getTextFromContents(
  helpers.getCandidateContents( candidates )
);
```

## Generating image content using an AI service

Coming soon.
