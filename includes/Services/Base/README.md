# AI Services Base Classes

Directory: `includes/Services/Base/`

This directory contains abstract base classes and generic implementations that provide foundational functionality for creating concrete AI services, models, and API clients within the AI Services plugin. These components are designed to be extended or utilized by specific service integrations (e.g., OpenAI, Anthropic, Google).

Many of these base classes implement interfaces from `includes/Services/Contracts/`.

## Purpose

The primary purpose of the code within this directory is to:

*   **Reduce Boilerplate:** Offer common structures and methods that are shared across different AI service and model implementations, reducing code duplication.
*   **Enforce Consistency:** Ensure that core functionalities like metadata handling, model instantiation, request option management, and configuration sanitization are handled uniformly.
*   **Provide Reusable Components:** Offer generic implementations, like the `Generic_AI_API_Client`, that can be readily configured and used by multiple service integrations.
*   **Simplify Development:** Abstract away common complexities, allowing developers to focus on the unique aspects of a particular AI service provider.

## Key Components

### `Abstract_AI_Model.php` (`Abstract_AI_Model`)

*   **Description:** An abstract base class for specific AI models. It implements the `Generative_AI_Model` contract.
*   **Key Features:**
    *   Manages model metadata (`Model_Metadata`) and request options.
    *   Provides final methods for getting the model slug and metadata, ensuring these are derived consistently from the `Model_Metadata` object.
    *   Requires concrete model implementations to set the metadata, typically in their constructor.

### `Abstract_AI_Service.php` (`Abstract_AI_Service`)

*   **Description:** An abstract base class for AI service providers. It implements the `Generative_AI_Service` contract.
*   **Key Features:**
    *   Manages service metadata (`Service_Metadata`).
    *   Provides a final implementation for `get_model()`, which includes logic for:
        *   Retrieving a cached list of available models (`cached_list_models()`).
        *   Selecting a model based on an explicit slug or by matching requested capabilities (`AI_Capabilities::get_model_slugs_for_capabilities()`).
        *   Allowing subclasses to define model preference via `sort_models_by_preference()`.
        *   Delegating the actual model instantiation to an abstract `create_model_instance()` method.
    *   Provides a default implementation for `is_connected()`, which attempts to list models for cloud-based services to verify credentials.
    *   Requires concrete service implementations to provide their metadata and implement `list_models()` and `create_model_instance()`.

### `Abstract_Generation_Config.php` (`Abstract_Generation_Config`)

*   **Description:** An abstract base class for representing and managing configuration options for generative AI models (e.g., temperature, max tokens). It implements the `Generation_Config` contract.
*   **Key Features:**
    *   Handles the sanitization of input arguments against a defined schema (`get_supported_args_definition()`, which must be implemented by subclasses).
    *   Manages default values for configuration parameters.
    *   Separates formally supported arguments from additional, pass-through arguments.
    *   Provides methods to retrieve individual arguments (`get_arg()`), all set arguments (`get_args()`), and additional arguments (`get_additional_args()`).
    *   Implements `to_array()` and a static `from_array()` factory method.

### `Generic_AI_API_Client.php` (`Generic_AI_API_Client`)

*   **Description:** A concrete, generic implementation of the `Generative_AI_API_Client` contract. It utilizes the `Generative_AI_API_Client_Trait` for common API client logic (like error handling and response processing).
*   **Key Features:**
    *   Configurable via constructor parameters for base URL, API version, API name, a `Request_Handler`, and an optional `Authentication` instance.
    *   Provides implementations for `create_get_request()` and `create_post_request()`, constructing `Get_Request` and `JSON_Post_Request` objects respectively.
    *   Handles request URL construction and applies default request options (e.g., timeout).
    *   Applies authentication to requests if an `Authentication` instance is provided.
    *   This class is designed to be a reusable API client for services that follow a common RESTful pattern and can be configured with standard HTTP request handlers and authentication mechanisms.

These base classes and generic implementations form a crucial layer in the plugin's architecture, promoting code reuse and a standardized approach to integrating various AI services.
