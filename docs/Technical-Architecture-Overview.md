---
title: Technical Architecture Overview
layout: page
---

This document outlines the technical architecture of the AI Services WordPress plugin.

The AI Services plugin provides a centralized infrastructure for integrating various third-party AI services into WordPress. It offers a unified API accessible via PHP, JavaScript (through `@wordpress/data` stores and React components), REST API, and WP-CLI. The core goal is to abstract the specifics of individual AI providers, allowing developers and users to interact with different AI capabilities (like text generation, image generation, function calling) in a consistent manner.

## Core Components

1. **PHP API (`includes/`):** The server-side foundation. It handles service registration, authentication, interaction with external AI APIs, data type definitions, REST endpoint logic, and WP-CLI commands.
2. **JavaScript API (`src/`):** The client-side interface, primarily using `@wordpress/data` stores for state management and React components for UI elements (like the Settings and Playground pages).
3. **REST API (`includes/Services/REST_Routes/`):** Exposes AI functionalities, allowing communication between the JavaScript frontend and the PHP backend, or for external applications to interact with the plugin's capabilities.
4. **WP-CLI Commands (`includes/Services/CLI/`):** Provides command-line access to AI functionalities.

## PHP Architecture (`includes/`)

* **Dependency Injection:** The plugin utilizes a **Service Container** (`includes/Services/Services_Service_Container_Builder.php`) built upon the `felixarntz/wp-oop-plugin-lib` library. This container manages the instantiation and provision of various services (like API clients, REST routes, admin pages, etc.) throughout the plugin, promoting loose coupling and testability.
* **Core Library (`wp-oop-plugin-lib`):** Leverages the `felixarntz/wp-oop-plugin-lib` Composer package for OOP wrappers around WordPress core functions, handling tasks like admin page creation, REST route registration, script/style loading, options management, and capability handling.
* **Service Abstraction:**
    * Defines interfaces like `Generative_AI_Service` and `Generative_AI_Model` (`includes/Services/Contracts/`) to ensure a consistent interaction pattern regardless of the underlying AI provider.
    * Uses decorators (`includes/Services/Decorators/`) to add common functionality (like caching via Transients API) to service implementations.
* **Service Implementations:** Contains specific logic for interacting with different AI providers (e.g., `includes/Anthropic/`, `includes/Google/`, `includes/OpenAI/`). Each implementation includes:
    * An API client (`*_AI_API_Client.php`) handling direct communication with the external API.
    * A service class (`*_AI_Service.php`) implementing `Generative_AI_Service` and acting as a factory for model instances.
    * Model classes (`*_AI_*_Model.php`) implementing `Generative_AI_Model` and capability-specific interfaces (e.g., `With_Text_Generation`, `With_Image_Generation`, `With_Function_Calling`) to handle specific AI tasks.
* **Data Types (`includes/Services/API/Types/`):** Defines standardized PHP classes for representing data structures used in AI interactions (e.g., `Content`, `Parts`, `Candidate`, `Tools`, `History`). This ensures consistency across different services and APIs.
* **REST API Implementation (`includes/Services/REST_Routes/`):** Defines specific REST route handlers extending `Abstract_REST_Route` from the core library. Routes cover:
    * Fetching service information (`Service_List_REST_Route`, `Service_Get_REST_Route`).
    * Generating content (`Service_Generate_Text_REST_Route`, `Service_Generate_Image_REST_Route`, `Service_Stream_Generate_Text_REST_Route`).
    * Managing chat history (`History_*_REST_Route`).
    * Providing plugin/user context (`Self_REST_Route`).
    * Uses Resource Schemas (`*_REST_Resource_Schema.php`) to define the structure and validation for REST responses.
* **Dependency Scoping (`php-scoper.inc.php`, `third-party/`):** Uses PHP-Scoper to prefix third-party PHP dependencies (like Guzzle) bundled within the `/third-party` directory. This prevents conflicts with other plugins that might bundle the same dependencies. The main plugin dependencies managed via Composer reside in `/vendor/`.

## JavaScript Architecture (`src/`)

* **State Management (`@wordpress/data`):** Uses Redux-based stores for managing application state:
    * `@ai-services/ai` (`src/ai/`): Core store for managing available services, models, and making AI requests.
    * `@ai-services/settings` (`src/settings/`): Manages plugin settings, primarily API keys.
    * `@ai-services/playground` (`src/playground-page/store/`): Manages state specific to the AI Playground screen (selected service/model, messages, function declarations, etc.).
* **UI Components:**
    * Relies heavily on standard WordPress components (`@wordpress/components`, `@wordpress/icons`, etc.).
    * Provides reusable components specific to the plugin in `src/components/` (e.g., `ApiKeyControl`, `Parts`, `PluginIcon`).
* **Build Process (`webpack.config.js`, `build/`):** Uses Webpack to bundle JavaScript and CSS assets. Each main folder in `src/` (e.g., `ai`, `settings`, `services-page`, `playground-page`) corresponds to a separate entry point, generating JS and CSS bundles in the `build/` directory. Asset manifests (`*.asset.php`) are generated for WordPress script/style dependency management.
* **Admin Pages:**
    * **Settings Page (`src/services-page/`):** React application allowing users to configure API keys for registered services. Uses the `@ai-services/settings` store.
    * **Playground Page (`src/playground-page/`):** React application providing an interface to experiment with different AI capabilities (text/image generation, function calling), services, and models. Uses the `@ai-services/playground` and `@ai-services/ai` stores.

## Data Flow Example (Playground Text Generation)

1. **User Interaction (JS):** User types a prompt in the Playground UI (`src/playground-page/components/PlaygroundMain/input.js`).
2. **State Update (JS):** An action is dispatched to the `@ai-services/playground` store to add the user message.
3. **API Request (JS):** An action triggers a call to `generateText` (or `streamGenerateText`) on the selected service model instance within the `@ai-services/ai` store.
4. **REST Request (JS -> PHP):** The JS store uses `@wordpress/api-fetch` to send a POST request to the relevant REST endpoint (e.g., `/ai-services/v1/services/{slug}/generate-text`).
5. **REST Handling (PHP):** The corresponding REST route (`Service_Generate_Text_REST_Route`) receives the request. It validates permissions and parameters.
6. **PHP API Call (PHP):** The REST route uses the `Services_API` (`ai_services()`) to get the appropriate `Generative_AI_Service` instance, then calls `get_model()` and `generate_text()` (or `stream_generate_text()`).
7. **Service Implementation (PHP):** The specific service implementation (e.g., `Google_AI_Service`) prepares the request for the external AI provider.
8. **External API Call (PHP):** The service's API client (e.g., `Google_AI_API_Client`) makes an HTTP request (using Guzzle via `HTTP_With_Streams`) to the external AI API.
9. **Response Handling (PHP):** The response from the external API is processed, standardized into the plugin's data types (`Candidates`, `Content`, `Parts`), and returned up the chain to the REST route.
10. **REST Response (PHP -> JS):** The REST route sends the standardized response back to the client.
11. **State Update (JS):** The `@wordpress/api-fetch` middleware updates the `@ai-services/playground` store with the AI model's response.
12. **UI Update (JS):** React components subscribed to the store re-render to display the response message (`src/playground-page/components/PlaygroundMain/messages.js`).

## Extensibility

* **Registering New Services:** Developers can implement the `Generative_AI_Service` interface and register their custom service implementation using the `Services_API::register_service()` method. This makes the custom service available alongside the built-in ones throughout the plugin's APIs and UI.
* **Filters and Actions:** Various WordPress filters and actions are available for customization (e.g., `ai_services_model_params` to modify model parameters, `ai_services_load_services_capabilities` to adjust user permissions).

## Directory Structure

* `/includes`: Core PHP classes (API, services, models, REST routes, WP-CLI, admin integration).
* `/src`: Source JavaScript (React) and SCSS files for admin UI and client-side API.
* `/build`: Compiled JavaScript and CSS assets generated by Webpack.
* `/third-party`: Bundled third-party PHP libraries managed by PHP-Scoper.
* `/vendor`: Composer-managed PHP dependencies (including `wp-oop-plugin-lib`).
* `/docs`: User and developer documentation.
* `/examples`: Sample plugins demonstrating how to use the AI Services API.
