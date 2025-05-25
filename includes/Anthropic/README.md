# Anthropic Integration

Directory: `includes/Anthropic/`

This directory contains the PHP classes responsible for integrating the Anthropic AI services into the plugin. It includes the API client for direct communication with Anthropic, the service class for managing Anthropic models, and the model class for text generation capabilities.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a client for making authenticated requests to the Anthropic API (`Anthropic_AI_API_Client.php`).
- Define and manage the Anthropic AI service, including listing available models and instantiating specific model types (`Anthropic_AI_Service.php`).
- Implement the functionalities of Anthropic's text generation models, adapting them to the plugin's common AI service interface (`Anthropic_AI_Text_Generation_Model.php`).
- Handle Anthropic-specific request and response formats, including transformations for chat history, function calling, multimodal inputs, and streaming.

## Architecture and Technical Decisions

- **API Client (`Anthropic_AI_API_Client.php`):**
    - Extends the `Generic_AI_API_Client` from `includes/Services/Base/` to reuse common API client logic.
    - Sets the Anthropic-specific `x-api-key` header for authentication.
    - Includes the `anthropic-version` header in all requests, as required by the Anthropic API.

- **Service Definition (`Anthropic_AI_Service.php`):**
    - Extends `Abstract_AI_Service` (from `includes/Services/Base/`) and uses the `With_API_Client_Trait` (from `includes/Services/Traits/`) for standard service behavior.
    - Instantiates `Anthropic_AI_API_Client` for communication.
    - The `list_models()` method fetches models from the Anthropic API. Since the API does not return model capabilities, these are currently hardcoded within this method. This might require updates if Anthropic changes its API or model capabilities.
    - Provides a `sort_models_by_preference()` method to order models, prioritizing newer and more capable ones like "Claude 3.5 Sonnet".

- **Text Generation Model (`Anthropic_AI_Text_Generation_Model.php`):**
    - Extends `Abstract_AI_Model` (from `includes/Services/Base/`) and implements various capability-specific contracts from `includes/Services/Contracts/` (e.g., `With_Text_Generation`, `With_Chat_History`, `With_Function_Calling`, `With_Web_Search`, `With_Multimodal_Input`).
    - Utilizes several traits from `includes/Services/Traits/` to manage model parameters like system instructions, text generation configuration, and tool configurations.
    - Implements methods for both standard (`send_generate_text_request()`) and streaming (`send_stream_generate_text_request()`) text generation.
    - Contains detailed logic for transforming the plugin's generic `Content`, `Parts`, `Text_Generation_Config`, `Tools`, and `Tool_Config` types (from `includes/Services/API/Types/`) into the specific format required by the Anthropic "messages" API. This includes handling different content part types (text, image, function call, function response) and mapping roles (e.g., `Content_Role::MODEL` to "assistant").
    - The `max_tokens` parameter has a default of `4096` as it's required by the Anthropic API. Temperature is capped at `1.0`.
    - Image data is transformed from base64 data URLs to raw base64 data, as expected by Anthropic.

## Key Interactions

- The `Anthropic_AI_Service` is registered with the main plugin services (see `includes/Services/`).
- When a request for an Anthropic model is made, `Anthropic_AI_Service` instantiates `Anthropic_AI_Text_Generation_Model`.
- `Anthropic_AI_Text_Generation_Model` uses `Anthropic_AI_API_Client` (obtained via `Anthropic_AI_Service`) to send requests to the Anthropic API.
- Data transformation to and from the Anthropic API format is a critical responsibility of `Anthropic_AI_Text_Generation_Model`, ensuring compatibility with the plugin's abstract AI interfaces defined in `includes/Services/API/` and `includes/Services/Contracts/`.
