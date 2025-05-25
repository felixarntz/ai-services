# AI Services Contracts

Directory: `includes/Services/Contracts/`

This directory houses a collection of PHP interfaces (contracts) that define the fundamental building blocks and expected behaviors for various components within the AI Services plugin. These contracts are crucial for establishing a consistent and extensible architecture.

## Purpose

The primary purpose of the code within this directory is to:

*   **Define Standardized APIs:** Ensure that different implementations of services, models, or clients adhere to a common set of methods and properties.
*   **Enable Polymorphism:** Allow different concrete classes to be used interchangeably as long as they implement the relevant contracts.
*   **Promote Decoupling:** Reduce direct dependencies between components by relying on abstractions (interfaces) rather than concrete implementations. This facilitates easier testing, maintenance, and extension of the plugin.
*   **Clarify Capabilities:** Provide clear definitions for specific AI capabilities (e.g., text generation, image generation, function calling) that models can support.

## Key Contracts

Below is an overview of the main interfaces found in this directory:

*   **`Authentication.php` (`Authentication`)**:
    Defines the contract for classes that handle authentication credentials for an AI service. It includes methods for authenticating a request and retrieving WordPress option definitions for storing credentials.

*   **`Generation_Config.php` (`Generation_Config`)**:
    Specifies the interface for classes representing configuration options for a generative AI model (e.g., temperature, max tokens). It extends `Arrayable` and `With_JSON_Schema`.

*   **`Generative_AI_API_Client.php` (`Generative_AI_API_Client`)**:
    Outlines the methods for a client that interacts with a generative AI web API. This includes creating GET/POST requests, making requests, processing responses (both regular and streamed), and creating standardized exceptions.

*   **`Generative_AI_Model.php` (`Generative_AI_Model`)**:
    Represents the contract for a specific generative AI model. It requires methods to get the model's slug and its metadata.

*   **`Generative_AI_Service.php` (`Generative_AI_Service`)**:
    Defines the interface for a generative AI service provider (e.g., OpenAI, Anthropic). This includes methods for getting the service slug, its metadata, checking connectivity, listing available models, and retrieving a specific model instance based on parameters and capabilities.

*   **`With_API_Client.php` (`With_API_Client`)**:
    A simple interface for services or models that utilize an AI API client, requiring a `get_api_client()` method.

*   **Capability-Specific Interfaces (`With_*`)**:
    These interfaces are typically implemented by `Generative_AI_Model` classes to indicate support for specific AI functionalities:
    *   **`With_Chat_History.php` (`With_Chat_History`)**: For models that support multi-turn chat sessions. Extends `With_Text_Generation`.
    *   **`With_Function_Calling.php` (`With_Function_Calling`)**: For models that support function calling capabilities.
    *   **`With_Image_Generation.php` (`With_Image_Generation`)**: For models capable of generating images.
    *   **`With_JSON_Schema.php` (`With_JSON_Schema`)**: For classes (like `Generation_Config`) that can provide a JSON schema for their expected input.
    *   **`With_Multimodal_Input.php` (`With_Multimodal_Input`)**: For models that can accept multimodal input (e.g., text and images).
    *   **`With_Multimodal_Output.php` (`With_Multimodal_Output`)**: For models that can produce multimodal output.
    *   **`With_Text_Generation.php` (`With_Text_Generation`)**: For models capable of generating text, including streaming responses.
    *   **`With_Web_Search.php` (`With_Web_Search`)**: For models that support web search capabilities.

By adhering to these contracts, developers can integrate new AI services or models into the plugin with greater ease and confidence, knowing they fit into the established architectural patterns.
