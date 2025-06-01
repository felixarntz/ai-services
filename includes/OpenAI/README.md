# OpenAI Integration

Directory: `includes/OpenAI/`

This directory contains the PHP classes responsible for integrating with the OpenAI API. It includes a service class that manages the connection and model discovery, and specific model classes for text generation and image generation, adapting the plugin's generic AI interfaces to OpenAI's specific request and response formats.

The classes herein handle API authentication, request formatting, response parsing, and capability mapping for various OpenAI models. They interact closely with the core AI service abstractions found in `includes/Services/Base/` and implement contracts from `includes/Services/Contracts/`.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a concrete implementation of the `Generative_AI_Service` contract for OpenAI.
- Offer specific `Generative_AI_Model` implementations for OpenAI's text and image generation capabilities.
- Translate the plugin's standardized AI operations (like generating text, generating images, handling chat history, function calling) into HTTP requests compatible with the OpenAI API.
- Parse responses from the OpenAI API and transform them into the plugin's standardized `Candidates` and `Content` object structures.
- Manage model-specific configurations and parameters, such as system instructions, generation settings, and tool configurations.
- List and categorize available OpenAI models, mapping their capabilities to the plugin's internal `AI_Capability` enum.

## Key Components

- **`OpenAI_AI_Service.php`**: This class extends `Abstract_AI_Service` and acts as the main entry point for interacting with the OpenAI service. It handles API client setup, lists available models from the OpenAI API (e.g., `gpt-4o`, `dall-e-3`), and instantiates the appropriate model classes (`OpenAI_AI_Text_Generation_Model` or `OpenAI_AI_Image_Generation_Model`) based on the requested capabilities. It also defines default API base URLs and versions.

- **`OpenAI_AI_Text_Generation_Model.php`**: This class extends `Abstract_AI_Model` and implements various capability interfaces like `With_Text_Generation`, `With_Chat_History`, `With_Function_Calling`, and `With_Multimodal_Input`. It is responsible for interacting with OpenAI's chat completions endpoint (`chat/completions`). It prepares request payloads by transforming `Content` objects (which can include text, images, and function calls/responses) and `Text_Generation_Config` into the format expected by OpenAI. It also processes both standard and streaming responses, converting them into `Candidates` objects.

- **`OpenAI_AI_Image_Generation_Model.php`**: This class also extends `Abstract_AI_Model` and implements `With_Image_Generation`. It interacts with OpenAI's image generation endpoint (`images/generations`). It prepares image generation parameters, including prompts and `Image_Generation_Config`, and processes the API response to extract generated images, handling different output formats like base64 encoded JSON or image URLs.

- **`OpenAI_AI_Text_To_Speech_Model.php`**: This class extends `Abstract_AI_Model` and implements `With_Text_To_Speech`. It interacts with OpenAI's audio speech endpoint (`audio/speech`). It prepares text to speech parameters, including the input text and `Text_To_Speech_Config`, and processes the API response to extract generated audio, handling different output formats.

## Architectural Concerns & Technical Decisions

- **API Client**: The `OpenAI_AI_Service` utilizes a `Generic_AI_API_Client` (from `includes/Services/Base/`) for making HTTP requests to the OpenAI API. Authentication is handled via an `Authentication` contract, typically an API key.
- **Model Capabilities**: Since the OpenAI `/models` endpoint does not explicitly detail the capabilities of each model (e.g., text generation, function calling, image generation), `OpenAI_AI_Service` includes hardcoded logic to determine these capabilities based on model ID patterns (e.g., `gpt-`, `dall-e-`).
- **Parameter Transformation**: Both model classes (`OpenAI_AI_Text_Generation_Model` and `OpenAI_AI_Image_Generation_Model`) contain significant logic for transforming the plugin's generic configuration objects (e.g., `Text_Generation_Config`, `Image_Generation_Config`, `Tool_Config`) and `Content` objects into the specific JSON structures required by the OpenAI API. This includes mapping roles (user, model, system), content part types, and tool parameters.
- **Response Handling**: The model classes parse JSON responses from OpenAI, extracting relevant data like generated text, image data (b64_json or URL), function calls, and finish reasons, and then structure this data into the plugin's `Candidates` and `Content` objects. Streaming responses for text generation are also supported.
- **Error Handling**: API errors and invalid responses are caught and re-thrown as `Generative_AI_Exception` instances, consistent with the plugin's error handling strategy defined in `includes/Services/Exception/`.
- **Extensibility**: The classes rely on traits from `includes/Services/Traits/` for common functionalities like handling API clients and model parameters, promoting code reuse.
- **Dependency Injection**: The service and model classes receive dependencies like the API client and model metadata via their constructors, adhering to dependency injection principles.
- **Base Abstractions**: The classes build upon base abstractions located in `includes/Services/Base/` (e.g., `Abstract_AI_Service`, `Abstract_AI_Model`) and implement contracts from `includes/Services/Contracts/`, ensuring consistency with other AI service integrations within the plugin.
