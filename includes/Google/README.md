# Google AI Service Integration

Directory: `includes/Google/`

This directory contains the PHP classes responsible for integrating Google's Generative AI services, such as the Gemini and Imagen models, into the AI Services plugin. It provides the concrete implementations for the Google AI service, its specific models (for text and image generation), and the API client tailored for Google's Generative Language API.

## Purpose

The primary purpose of the code within this directory is to:

- Establish communication with the Google Generative Language API.
- Define and manage Google-specific AI models, translating the plugin's generic AI operations into requests compatible with Google's API.
- Handle Google-specific API responses and transform them into the plugin's common data structures.
- Implement features like text generation, image generation, chat history, function calling, and multimodal interactions for Google AI models.

## Architecture

The classes in this directory extend and implement core abstractions provided by the `includes/Services/` directory, ensuring consistency with other AI service integrations.

- **`Google_AI_Service.php`**: This is the main service class for Google AI.
    - It extends `Abstract_AI_Service` (from `includes/Services/Base/`).
    - It is responsible for listing available Google AI models and instantiating the appropriate model classes (`Google_AI_Text_Generation_Model` or `Google_AI_Image_Generation_Model`) based on their capabilities.
    - It uses `Google_AI_API_Client` for all API communications.

- **`Google_AI_API_Client.php`**: This class handles direct HTTP interactions with the Google Generative Language API.
    - It extends `Generic_AI_API_Client` (from `includes/Services/Base/`).
    - It customizes request headers (e.g., `X-Goog-Api-Key` for authentication, `X-Goog-Api-Client` for client identification) and URL formatting (e.g., appending `?alt=sse` for streaming).

- **`Google_AI_Text_Generation_Model.php`**: Represents Google AI models capable of text generation, chat, function calling, and multimodal input/output (e.g., Gemini models).
    - It extends `Abstract_AI_Model` (from `includes/Services/Base/`).
    - It implements various contracts from `includes/Services/Contracts/` such as `With_Text_Generation`, `With_Chat_History`, `With_Function_Calling`, `With_Multimodal_Input`, and `With_Multimodal_Output`.
    - It transforms generic requests and configurations (like `Text_Generation_Config`) into Google API-specific formats, including handling of tools, tool configurations, system instructions, and safety settings.
    - It processes responses, including streaming data and various content part types (text, inline data, file data, function calls/responses).

- **`Google_AI_Image_Generation_Model.php`**: Represents Google AI models capable of image generation (e.g., Imagen models).
    - It extends `Abstract_AI_Model` (from `includes/Services/Base/`).
    - It implements `With_Image_Generation` from `includes/Services/Contracts/`.
    - It handles image generation specific parameters and processes image data from API responses.

- **`Types/` subdirectory**: Contains PHP classes representing data structures specific to the Google AI API, such as `Safety_Setting.php`. For more details, refer to `includes/Google/Types/`.

## Technical Decisions

Key technical decisions implemented in this directory include:

- **API Client Customization**: The `Google_AI_API_Client` adapts the generic API client behavior for Google's specific requirements, such as custom authentication headers and URL parameters for streaming.
- **Model Specialization**: Separate model classes (`Google_AI_Text_Generation_Model` and `Google_AI_Image_Generation_Model`) are used to handle the distinct capabilities and API interactions of different types of Google AI models.
- **Parameter Transformation**: Model classes are responsible for transforming generic configuration objects (e.g., `Text_Generation_Config`, `Image_Generation_Config` from `includes/Services/API/Types/`) into the precise JSON structures expected by the Google API. This includes:
    - Adjusting temperature scales (Google's 0.0-2.0 vs. internal 0.0-1.0).
    - Formatting function declarations and tool configurations, including removing `additionalProperties` from JSON schemas for function parameters as Google's API rejects it.
    - Handling specific content part transformations, like ensuring function responses are structured as objects.
- **Capability-based Model Instantiation**: The `Google_AI_Service` uses the `AI_Capabilities` utility (from `includes/Services/Util/`) to determine which model class to instantiate based on the advertised capabilities of a model slug.
- **Model Preference Sorting**: The `Google_AI_Service` includes logic to sort available models by a predefined preference (e.g., prioritizing newer, non-experimental, and more cost-effective models).
- **Streaming Support**: Implemented for text generation models by interacting with the `:streamGenerateContent` endpoint and processing Server-Sent Events (SSE).
- **Error Handling**: Leverages the exception handling mechanisms defined in `includes/Services/Exception/` and `includes/Services/Base/` to provide consistent error reporting.
- **Timeout Adjustment**: For potentially long-running operations like image generation, the default request timeout is increased in `Google_AI_Image_Generation_Model`.

This directory relies heavily on the abstractions and contracts defined in:

- `includes/Services/Base/`
- `includes/Services/Contracts/`
- `includes/Services/API/`
- `includes/Services/Util/`
