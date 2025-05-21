# AI Services Utility Classes

Directory: `includes/Services/Util/`

This directory contains various utility classes that provide helper functionalities used across the AI Services plugin, particularly within the services layer. These classes are generally stateless and offer static methods for common operations.

## Purpose

The primary purpose of the code within this directory is to:

- Provide utility classes for various purposes (see below).

## Files and Purpose

### `AI_Capabilities.php`

The `Felix_Arntz\AI_Services\Services\Util\AI_Capabilities` class provides static utility methods to determine and manage the AI capabilities supported by different AI models.

-   **Purpose**:
    -   To ascertain the features (e.g., text generation, image generation, chat history, function calling, multimodal input/output) that a specific AI model class or instance supports.
    -   To find model slugs or model class names that satisfy a given set of required capabilities.
-   **Architecture**:
    -   It relies on models implementing specific interfaces from the `includes/Services/Contracts/` directory (e.g., `With_Text_Generation`, `With_Chat_History`) to declare their capabilities.
    -   Interacts with `Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability` and `Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata`.
-   **Technical Decisions**:
    -   Capabilities are identified by checking `class_implements()` for model classes or `instanceof` for model instances against the predefined capability interfaces.
    -   Throws `InvalidArgumentException` if no models are found that satisfy the requested capabilities.

### `Data_Encryption.php`

The `Felix_Arntz\AI_Services\Services\Util\Data_Encryption` class is responsible for encrypting and decrypting data, primarily intended for securing sensitive information such as API keys.

-   **Purpose**: To provide a secure way to store and retrieve confidential data.
-   **Architecture**:
    -   Utilizes the `openssl` PHP extension with the `aes-256-ctr` encryption method.
    -   Requires an encryption key and salt, which can be provided during instantiation or defaulted from WordPress constants (`AI_SERVICES_ENCRYPTION_KEY`, `AI_SERVICES_ENCRYPTION_SALT`, `LOGGED_IN_KEY`, `LOGGED_IN_SALT`).
-   **Technical Decisions**:
    -   If the `openssl` extension is not available, data is returned unencrypted/undecrypted as a fallback.
    -   The salt is appended to the data before encryption and verified during decryption to ensure data integrity and correct key usage.
    -   Uses `base64_encode`/`base64_decode` for storing the encrypted binary data as a string.
    -   Includes fallback test keys/salts if no constants are defined, though this scenario implies a non-live or insecure environment.

### `Formatter.php`

The `Felix_Arntz\AI_Services\Services\Util\Formatter` class offers static methods for formatting and validating user prompts and system instructions into a consistent structure of `Content` objects.

-   **Purpose**: To standardize various input formats for AI prompts (strings, `Parts` instances, `Content` instances) into a uniform `Content[]` structure suitable for AI model processing.
-   **Architecture**:
    -   Handles validation of content against model capabilities (e.g., support for chat history, multimodal input) defined in `Felix_Arntz\AI_Services\Services\Util\AI_Capabilities`.
    -   Uses types from `includes/Services/API/Types/` such as `Content`, `Parts`, and `Text_Part`, and enums like `Felix_Arntz\AI_Services\Services\API\Enums\Content_Role`.
-   **Technical Decisions**:
    -   `format_and_validate_new_contents()` is the primary method, ensuring prompts adhere to model constraints (e.g., first message from user, multimodal support).
    -   Throws `InvalidArgumentException` for invalid inputs or if content types are unsupported by the target model.

### `Strings.php`

The `Felix_Arntz\AI_Services\Services\Util\Strings` class provides static utility methods for common string manipulations.

-   **Purpose**: To offer reusable helper functions for string operations.
-   **Architecture**: A stateless utility class.
-   **Technical Decisions**: Currently includes a method `snake_case_to_camel_case()` for converting string casing.

### `Transformer.php`

The `Felix_Arntz\AI_Services\Services\Util\Transformer` class provides static methods for transforming data structures, specifically `Content` objects and `Generation_Config` parameters, using callable transformers.

-   **Purpose**: To allow flexible and dynamic transformation of AI-related data objects into different formats or structures as required by specific AI service providers or internal logic.
-   **Architecture**:
    -   Accepts an array of callable transformers. Each key in the transformer array corresponds to a key in the output, and the callable defines how to derive its value.
    -   Operates on `Felix_Arntz\AI_Services\Services\API\Types\Content` and `Felix_Arntz\AI_Services\Services\Contracts\Generation_Config` objects.
-   **Technical Decisions**:
    -   `transform_content()`: Transforms a `Content` object based on provided callables.
    -   `transform_generation_config_params()`: Merges a `Generation_Config` object into an existing array of parameters, applying transformers. Existing parameters in the input array take precedence.
    -   Transformed values are only included in the output if they are truthy.
    -   Throws `InvalidArgumentException` if any provided transformer is not callable.

## Related Directories

-   `includes/Services/API/Types/`: Contains data type classes like `Content` and `Parts` used by `Formatter` and `Transformer`.
-   `includes/Services/API/Enums/`: Contains enums like `AI_Capability` and `Content_Role` used by `AI_Capabilities` and `Formatter`.
-   `includes/Services/Contracts/`: Contains interfaces that define model capabilities, which are checked by `AI_Capabilities`.
