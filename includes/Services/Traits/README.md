# AI Services Model Implementation Traits

Directory: `includes/Services/Traits/`

This directory houses a collection of PHP traits designed to provide reusable functionality for classes within the AI Services plugin, particularly for AI models, API clients, and classes implementing specific service capabilities. These traits promote code reuse, consistency, and adherence to defined contracts.

## Purpose

The primary purpose of the code within this directory is to:

- Offer traits to facilitate:
    - **Code Reusability (DRY Principle):** To abstract common patterns and logic into reusable units, reducing duplication across different service and model implementations.
    - **Consistency:** To ensure that features like API client interaction, parameter handling, and capability implementations (e.g., text generation, image generation, chat) are handled uniformly.
    - **Interface Adherence:** Many traits provide common implementations for methods defined in interfaces located in `includes/Services/Contracts/`. This helps ensure that classes using these traits correctly fulfill their contractual obligations.
    - **Standardized Configuration:** Several traits are dedicated to managing specific configuration parameters for AI models, often sourced from model parameters and typed using objects from `includes/Services/API/Types/`.

## Overview of Traits

Below is a summary of the traits available in this directory:

-   **`Generative_AI_API_Client_Trait.php`**: Provides core functionality for API client classes that implement the `Generative_AI_API_Client` interface (from `includes/Services/Contracts/`). It handles request sending (including streaming), response processing, and standardized error/exception creation. Classes using this trait must implement `get_request_handler()` and `get_api_name()`.

-   **`Model_Param_Image_Generation_Config_Trait.php`**: Manages image generation configuration (`Image_Generation_Config` from `includes/Services/API/Types/`) for AI models. It includes helpers to set this configuration from model parameters.

-   **`Model_Param_System_Instruction_Trait.php`**: Handles system instructions (`Content` from `includes/Services/API/Types/`) for AI models. It provides methods to set the system instruction, often from model parameters via `Formatter` utility from `includes/Services/Util/`.

-   **`Model_Param_Text_Generation_Config_Trait.php`**: Manages text generation configuration (`Text_Generation_Config` from `includes/Services/API/Types/`) for AI models. It includes helpers to set this configuration from model parameters.

-   **`Model_Param_Tool_Config_Trait.php`**: Manages tool configuration (`Tool_Config` from `includes/Services/API/Types/`) for AI models, allowing them to define how function calling or tools should behave.

-   **`Model_Param_Tools_Trait.php`**: Manages the set of available tools (`Tools` from `includes/Services/API/Types/`) for AI models that support function calling.

-   **`With_API_Client_Trait.php`**: Implements the `With_API_Client` interface (from `includes/Services/Contracts/`), providing standardized injection and access to a `Generative_AI_API_Client` instance.

-   **`With_Chat_History_Trait.php`**: Implements the `With_Chat_History` interface (from `includes/Services/Contracts/`), offering a `start_chat()` method that initializes a `Chat_Session` (from `includes/Services/API/Types/`).

-   **`With_Image_Generation_Trait.php`**: Implements the `With_Image_Generation` interface (from `includes/Services/Contracts/`). It provides the `generate_image()` method, handling content sanitization and delegating the actual API call to an abstract `send_generate_image_request()` method.

-   **`With_Text_Generation_Trait.php`**: Implements the `With_Text_Generation` interface (from `includes/Services/Contracts/`). It provides `generate_text()` and `stream_generate_text()` methods, handling content sanitization and delegating API calls to abstract `send_generate_text_request()` and `send_stream_generate_text_request()` methods.

## Common Patterns and Technical Decisions

-   **Abstract Methods for Specialization:** Traits like `Generative_AI_API_Client_Trait`, `With_Image_Generation_Trait`, and `With_Text_Generation_Trait` use `abstract protected` methods to defer provider-specific implementation details to the consuming class.
-   **Final Public Methods:** Many public methods provided by these traits are marked `final` to ensure consistent behavior and prevent unintended overrides.
-   **Typed Properties and Methods:** Strict typing is enforced for properties, method parameters, and return values, leveraging custom types from `includes/Services/API/Types/`.
-   **Configuration from Model Parameters:** Several `Model_Param_*` traits include protected helper methods (e.g., `set_image_generation_config_from_model_params`) to initialize their respective configurations from a generic `model_params` array.
-   **Dependency on Core Types and Contracts:** These traits are tightly coupled with interfaces in `includes/Services/Contracts/` and data transfer objects (DTOs) in `includes/Services/API/Types/`. Utility functions from `includes/Services/Util/` (like `Formatter` and `AI_Capabilities`) are also used.

## Related Directories

-   `includes/Services/Contracts/`: Contains interfaces that these traits often help implement.
-   `includes/Services/API/Types/`: Defines data structures (DTOs) used extensively by these traits for configuration and data exchange.
-   `includes/Services/Util/`: Provides utility classes used by some traits.
-   `includes/Services/Base/`: Abstract base classes for services and models often utilize these traits.
