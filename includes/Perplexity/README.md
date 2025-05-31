# Perplexity AI Service Integration

Directory: `includes/Perplexity/`

This directory contains the PHP classes responsible for integrating with the Perplexity AI API. It includes a service class that manages the connection and model discovery, and a model class for text generation, adapting the plugin's generic AI interfaces to Perplexity's specific request and response formats.

The classes herein handle API authentication, request formatting, response parsing, and capability mapping for various Perplexity models. They interact closely with the core AI service abstractions found in `includes/Services/Base/` and implement contracts from `includes/Services/Contracts/`.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a concrete implementation of the `Generative_AI_Service` contract for Perplexity.
- Offer a specific `Generative_AI_Model` implementation for Perplexity's text generation capabilities, including chat history, web search, and potentially multimodal input.
- Translate the plugin's standardized AI operations into HTTP requests compatible with the Perplexity API.
- Parse responses from the Perplexity API and transform them into the plugin's standardized `Candidates` and `Content` object structures.
- Manage model-specific configurations and parameters.
- List and categorize available Perplexity models, mapping their capabilities to the plugin's internal `AI_Capability` enum.

## Key Components

- **`Perplexity_AI_Service.php`**: This class extends `Abstract_AI_Service` and acts as the main entry point for interacting with the Perplexity service. It handles API client setup, lists available models (currently hardcoded based on Perplexity's documentation), and instantiates the `Perplexity_AI_Text_Generation_Model` class.

- **`Perplexity_AI_Text_Generation_Model.php`**: This class extends `Abstract_AI_Model` and implements various capability interfaces like `With_Text_Generation`, `With_Chat_History`, `With_Multimodal_Input`, and `With_Web_Search`. It is responsible for interacting with Perplexity's chat completions endpoint. It prepares request payloads by transforming `Content` objects and `Text_Generation_Config` into the format expected by Perplexity. It also processes both standard and streaming responses.

## Architectural Concerns & Technical Decisions

- **API Client**: The `Perplexity_AI_Service` utilizes a `Generic_AI_API_Client` (from `includes/Services/Base/`) for making HTTP requests to the Perplexity API. Authentication is handled via an `Authentication` contract, expecting a Bearer token.
- **Model Listing**: Currently, models are hardcoded in `Perplexity_AI_Service.php` as Perplexity does not seem to offer a public API endpoint for listing models. This list should be updated if such an endpoint becomes available or when Perplexity updates their model offerings.
- **Capability Mapping**: Capabilities for listed models are determined based on information from Perplexity's documentation and model naming conventions (e.g., "online" models supporting web search).
- **Parameter Transformation**: The `Perplexity_AI_Text_Generation_Model` class is responsible for transforming the plugin's generic configuration objects and `Content` objects into the specific JSON structures required by the Perplexity API. This includes handling roles, system prompts, and generation parameters. Perplexity-specific features like search parameters (`search_quality`, `search_recency`, `search_domains`) are expected to be passed via `Text_Generation_Config`'s additional arguments.
- **Multimodal Input**: While `MULTIMODAL_INPUT` is a declared capability, the current scaffolding for `Perplexity_AI_Text_Generation_Model` has basic text concatenation for message parts. Actual multimodal input (e.g., images) will require further investigation into Perplexity's API support for such features within the chat completions endpoint and corresponding implementation.
- **Streaming Support**: Basic scaffolding for streaming responses from the chat completions endpoint is included.
- **Error Handling**: Leverages the plugin's standard exception handling.
- **Base Abstractions and Traits**: The classes build upon base abstractions from `includes/Services/Base/` and utilize traits from `includes/Services/Traits/` for common functionalities.
