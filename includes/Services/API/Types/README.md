# AI Services API: Data Types

Directory: `includes/Services/API/Types/`

This directory contains PHP classes that represent various data structures (types) used throughout the AI Services API. These classes primarily function as value objects, Data Transfer Objects (DTOs), and configuration objects, ensuring data consistency and providing clear contracts for API interactions.

## Purpose

The primary purpose of the code within this directory is to:

- Define the data types relevant for communication and configuration with the various AI services
- Expose these types as part of the AI Services plugin's public API to other plugin developers

## Overall Architecture and Key Concepts

The data types in this directory adhere to several architectural patterns:

-   **Value Objects/DTOs**: Many classes are `final` and designed to encapsulate specific data structures. They often provide:
    -   A `from_array(array $data)` static factory method for easy instantiation from associative arrays.
    -   A `to_array(): array` method for serializing the object's data back into an array.
    -   Examples include `Blob`, `Candidate`, `Content`, `History_Entry`, `Model_Metadata`, and `Service_Metadata`.

-   **Collections**: Classes like `Candidates`, `Parts`, and `Tools` serve as typed collections for other data type objects within this directory. They typically implement the `Collection` and `Arrayable` interfaces (from the `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts` namespace) to provide iterable and array-convertible functionalities.

-   **Configuration Objects**: These classes are used to pass specific settings and options to AI models or services.
    -   `Image_Generation_Config`, `Text_Generation_Config`, and `Tool_Config` are key examples.
    -   `Image_Generation_Config` and `Text_Generation_Config` extend a common base class `Abstract_Generation_Config` (located in `includes/Services/Base/`).

-   **JSON Schema**: Many data type classes implement the `With_JSON_Schema` interface (from `Felix_Arntz\AI_Services\Services\Contracts`) and provide a static `get_json_schema(): array` method. This schema can be used for data validation, generating documentation, or dynamically building user interfaces.

## Detailed Structures

### 1. Content Structure

The API uses a hierarchical structure to represent content exchanged with AI models:

-   **`Content.php`**: Represents a single unit of communication, such as a user's message or an AI model's response. It contains a `role` (e.g., 'user', 'model') and a collection of `Parts`.
-   **`Parts.php`**: A collection class holding one or more `Part` objects that make up the `Content`.
-   **`Contracts/Part.php`**: An interface defining the contract for all content parts. It requires implementing `Arrayable` and a `set_data(array $data)` method.
-   **`Parts/Abstract_Part.php`**: An abstract base class for concrete `Part` implementations, providing common functionality like `from_array()` and `to_array()`.
-   **Concrete Part Types** (located in `Parts/` directory):
    -   `Text_Part.php`: Represents plain textual content.
    -   `Inline_Data_Part.php`: Represents binary data (e.g., images) embedded directly within the content as a base64-encoded string, along with its MIME type.
    -   `File_Data_Part.php`: Represents a reference to an external file via a URI, along with its MIME type.
    -   `Function_Call_Part.php`: Represents a request from the AI model to execute a specific function, including the function's name (or ID) and arguments.
    -   `Function_Response_Part.php`: Represents the result of a function execution, provided back to the AI model, including the function's name (or ID) and the response data.

### 2. Tooling Structure

The API allows defining tools that AI models can utilize, particularly for function calling:

-   **`Tools.php`**: A collection class holding one or more `Tool` objects that can be provided to an AI model.
-   **`Contracts/Tool.php`**: An interface defining the contract for all tool types. It requires implementing `Arrayable` and a `set_data(array $data)` method.
-   **`Tools/Abstract_Tool.php`**: An abstract base class for concrete `Tool` implementations.
-   **Concrete Tool Types** (located in `Tools/` directory):
    -   `Function_Declarations_Tool.php`: Allows defining a list of functions (with names, descriptions, and parameter schemas) that the AI model is permitted to call.

### 3. Chat and History Management

Classes related to managing conversations and their history:

-   **`Chat_Session.php`**: Manages an interactive chat with a generative AI model. It handles the conversation history and provides methods like `send_message()` and `stream_send_message()` to interact with the model.
-   **`History.php`**: Represents a persisted chat history, typically identified by a `feature` and a `slug`. It contains a list of `History_Entry` objects.
-   **`History_Entry.php`**: Represents a single turn or message within a `History` object, primarily composed of a `Content` object and any additional metadata.

### 4. Metadata Objects

Classes used to describe AI models and services:

-   **`Model_Metadata.php`**: Stores descriptive information about a specific AI model, including its unique `slug`, human-readable `name`, and a list of supported `capabilities` (e.g., 'text_generation', 'image_generation').
-   **`Service_Metadata.php`**: Stores descriptive information about an AI service provider, including its `slug`, `name`, `credentials_url` (for API key setup), `type` (e.g., 'cloud', 'server'), and a list of overall `capabilities` it supports.

### 5. Configuration Objects

These classes define specific configurations for different AI operations:

-   **`Image_Generation_Config.php`**: Holds configuration parameters specific to image generation, such as `responseMimeType`, `candidateCount`, `aspectRatio`, and `responseType`.
-   **`Text_Generation_Config.php`**: Holds configuration parameters for text generation, such as `stopSequences`, `responseMimeType`, `candidateCount`, `maxOutputTokens`, `temperature`, `topP`, `topK`, etc.
-   **`Tool_Config.php`**: Configures how tools (especially function calling) are used by the AI model, with options like `functionCallMode` (e.g., 'auto', 'any') and `allowedFunctionNames`.

## Top-Level Class Descriptions

-   **`Blob.php`**: Represents a binary data blob (e.g., from a file) with its binary data and MIME type. Includes a static factory `from_file()`.
-   **`Candidate.php`**: Represents a single potential response (candidate) from an AI model, containing `Content` and additional metadata.
-   **`Candidates.php`**: A collection of `Candidate` objects.
-   **`Chat_Session.php`**: Manages an interactive chat session with an AI model, including history.
-   **`Content.php`**: Represents a unit of content in a conversation, defined by a `role` and a collection of `Parts`.
-   **`History.php`**: Represents a persisted chat history for a specific feature.
-   **`History_Entry.php`**: Represents a single entry within a `History` object.
-   **`Image_Generation_Config.php`**: Configuration options for image generation requests.
-   **`Model_Metadata.php`**: Metadata describing an AI model.
-   **`Parts.php`**: A collection of `Part` objects that make up `Content`.
-   **`Service_Metadata.php`**: Metadata describing an AI service provider.
-   **`Text_Generation_Config.php`**: Configuration options for text generation requests.
-   **`Tool_Config.php`**: Configuration for how AI models should use tools (e.g., function calling).
-   **`Tools.php`**: A collection of `Tool` objects available to an AI model.

## Key Technical Decisions

-   **Immutability and Value Objects**: Most data type classes are `final` to encourage immutability or prevent unintended extension, ensuring they act as reliable value carriers.
-   **Serialization/Deserialization**: The consistent use of `from_array()` static factory methods and `to_array()` instance methods provides a standardized way to create objects from array data and serialize them back, facilitating data transfer and storage.
-   **Interface-Driven Design**: Adherence to interfaces like `Arrayable` and `Collection` (from the `wp-oop-plugin-lib` dependency), as well as custom interfaces like `Part`, `Tool`, and `With_JSON_Schema`, promotes polymorphism and clear contracts.
-   **Structured Composition**: Complex data like AI content and tools are built using a clear compositional hierarchy (e.g., `Content` is composed of `Parts`, which are specific `Part` types; `Tools` is a collection of specific `Tool` types).
-   **Schema Provision**: The inclusion of `get_json_schema()` methods in many types allows for runtime schema inspection, useful for validation, UI generation, or API documentation.
-   **Typed Collections**: Using dedicated collection classes (e.g., `Candidates`, `Parts`, `Tools`) instead of generic arrays helps maintain type safety and provides domain-specific collection manipulation methods.
