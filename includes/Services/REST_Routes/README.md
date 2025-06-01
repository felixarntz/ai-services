# REST API Routes

Directory: `includes/Services/REST_Routes/`

This directory contains PHP classes responsible for defining and handling the WordPress REST API endpoints for the AI Services plugin. These routes expose functionalities related to managing AI services, interacting with AI models for content generation (text and images), managing feature-specific interaction histories, and retrieving general plugin information.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a structured and secure way to interact with the AI Services plugin's features programmatically via the WordPress REST API.
- Define endpoints for listing, retrieving, and interacting with AI services, including text generation, image generation, and streamed text generation.
- Offer CRUD (Create, Read, Update, Delete) operations for managing feature-specific interaction histories.
- Expose a "self" endpoint to provide plugin metadata and current user capabilities.
- Define resource schemas that dictate the structure and validation of data exchanged through these REST API endpoints.
- Enforce permission checks based on user capabilities to ensure secure access to plugin functionalities.

## Key Components

This directory primarily consists of two types of classes:

-   **REST Route Classes:** These classes extend `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route` and define specific API endpoints. Each class typically specifies:
    -   The route base (URL pattern).
    -   Supported HTTP methods (e.g., `GET`, `POST`, `DELETE`).
    -   Permission checks (`check_permissions` method).
    -   Request handling logic (`handle_request` method).
    -   Expected request arguments (`args` and `global_args` methods).
-   **REST Resource Schema Classes:** These classes extend `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Resource_Schema` and define the structure, properties, and links for the data (resources) returned by the API endpoints. They are crucial for data validation and ensuring consistent API responses. Examples include `Service_REST_Resource_Schema.php` and `History_REST_Resource_Schema.php`.

## Route Categories

The REST API routes defined in this directory can be broadly categorized as:

### Service Management and Interaction

These routes allow clients to discover and use AI services.

-   `Service_List_REST_Route.php`: Lists all registered AI services.
-   `Service_Get_REST_Route.php`: Retrieves details for a specific AI service.
-   `Service_Generate_Text_REST_Route.php`: Handles requests for generating text content from a specified service and model.
-   `Service_Stream_Generate_Text_REST_Route.php`: Handles requests for generating text content with a streamed response.
-   `Service_Generate_Image_REST_Route.php`: Handles requests for generating images from a specified service and model.
-   `Service_Text_To_Speech_REST_Route.php`: Handles requests for transforming text to speech from a specified service and model.
-   `Service_Generate_Content_REST_Route.php`: An abstract base class for content generation routes, providing common functionality.

The schema for service-related data is defined in `Service_REST_Resource_Schema.php`. Core interactions with AI services are managed via the `Services_API` class, typically found in `includes/Services/Services_API.php`. Service data is often represented by `Service_Entity` from `includes/Services/Entities/`.

### History Management

These routes provide CRUD operations for interaction histories associated with specific plugin features.

-   `History_List_REST_Route.php`: Lists histories for a given feature.
-   `History_Get_REST_Route.php`: Retrieves a specific history.
-   `History_Update_REST_Route.php`: Creates or updates a history.
-   `History_Delete_REST_Route.php`: Deletes a history.

The schema for history-related data is defined in `History_REST_Resource_Schema.php`. History data persistence is handled via `History_Persistence`, accessible through `includes/Services/API/Helpers.php`. The data itself is often represented by `History_Entity` from `includes/Services/Entities/`.

### Plugin Information

-   `Self_REST_Route.php`: Provides general information about the AI Services plugin, such as version, URLs, and the current user's capabilities related to the plugin. This is useful for frontend interfaces to adapt their UI based on user permissions and plugin state.

## Technical Decisions

-   **Abstraction via `wp-oop-plugin-lib`:** The routes and schemas leverage base classes from the `wp-oop-plugin-lib` (prefixed under `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\`). This promotes a consistent, object-oriented approach to defining REST API endpoints.
-   **Capability-based Access Control:** All routes implement `check_permissions` methods that verify user capabilities (e.g., `ais_access_services`, `ais_manage_services`) before allowing access, ensuring secure operation.
-   **JSON Schema for Data Validation:** Request arguments and response structures are defined using JSON Schema, which is utilized by the base classes for validation and documentation. The specific type definitions for API request/response bodies can be found in `includes/Services/API/Types/`.
-   **Dependency Injection:** Constructors often receive dependencies like `Services_API`, `Current_User`, and resource schema instances, following DI principles.
-   **Clear Separation of Concerns:** Route handling, permission checks, and data schema definition are separated into distinct classes and methods.
-   **Specific Endpoints for Operations:** Instead of generic CRUD endpoints for all actions, specific action-oriented endpoints like `:generate-text`, `:stream-generate-text`, and `:generate-image` are used for clarity and to accommodate different request/response structures for different AI operations.
