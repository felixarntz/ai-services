# XAI

Directory: `includes/XAI/`

This directory contains the implementation for the xAI AI service, including its service class and model classes for text and image generation. It provides the necessary components to interact with the xAI API and utilize its AI capabilities within the AI Services plugin.

## Purpose

The primary purpose of the code within this directory is to:

-   Implement the xAI AI service, defining how to interact with the xAI API.
-   Define the AI models offered by xAI, specifically for text and image generation.
-   Handle authentication and API requests to the xAI service.
-   Translate the generic AI service requests into xAI-specific API calls.
-   Sort model slugs by preference.

## Classes

-   `XAI_AI_Service`: This class is the main entry point for the xAI AI service. It handles authentication, lists available models, and creates model instances. It extends `Abstract_AI_Service` and implements `With_API_Client`.
-   `XAI_AI_Text_Generation_Model`: This class represents an xAI text generation AI model. It extends `OpenAI_Compatible_AI_Text_Generation_Model` and implements `With_Function_Calling`, `With_Multimodal_Input`, and `With_Web_Search`.
-   `XAI_AI_Image_Generation_Model`: This class represents an xAI image generation AI model. It extends `OpenAI_Compatible_AI_Image_Generation_Model`.

## Architecture

The `XAI_AI_Service` class utilizes a `Generic_AI_API_Client` to communicate with the xAI API. It defines the base URL and API version for the service. The `list_models` method retrieves the available models from the xAI API and maps them to `Model_Metadata` objects. The `create_model_instance` method creates instances of the `XAI_AI_Text_Generation_Model` or `XAI_AI_Image_Generation_Model` classes based on the model's capabilities.

The `XAI_AI_Text_Generation_Model` class extends the `OpenAI_Compatible_AI_Text_Generation_Model` and implements the `With_Function_Calling`, `With_Multimodal_Input`, and `With_Web_Search` contracts. It uses the `OpenAI_Compatible_Text_Generation_With_Function_Calling_Trait` to handle function calling functionality.

The `XAI_AI_Image_Generation_Model` class extends the `OpenAI_Compatible_AI_Image_Generation_Model`.

This directory relies on the interfaces and abstract classes defined in `includes/Services/Contracts/` and `includes/Services/Base/`.
