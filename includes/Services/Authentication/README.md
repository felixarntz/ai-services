# AI Services Authentication

Directory: `includes/Services/Authentication/`

This directory contains the mechanisms for authenticating requests to various AI services.

## Purpose

The primary purpose of the code within this directory is to:

- Provide standardized ways to handle authentication for different AI service providers.

## Architecture and Technical Decisions

### `API_Key_Authentication.php`

This file defines the `API_Key_Authentication` class, which implements the `Felix_Arntz\AI_Services\Services\Contracts\Authentication` interface. It provides a concrete implementation for services that use API keys for authentication.

Key aspects of this implementation:

-   **Contract-Based:** Implements the `Authentication` interface, ensuring a consistent method signature (`authenticate()`) for applying authentication details to an HTTP request.
-   **Flexible Header Configuration:**
    -   Allows specifying the HTTP header name used to transmit the API key via the `set_header_name()` method.
    -   Defaults to the `Authorization` header.
    -   If the `Authorization` header is used, the API key is automatically prefixed with `Bearer `. For other headers, the key is sent as-is.
-   **WordPress Option Integration:**
    -   Provides a static method `get_option_definitions( string $service_slug ): array`.
    -   This method returns a standardized definition array for creating WordPress options to store the API key for a specific service. This promotes consistency in how API keys are managed within the WordPress admin area and database. The option slug is generated as `ais_{$service_slug}_api_key`.
    -   The options are defined to be `string`, default to an empty string, be available in the REST API (`show_in_rest => true`), and autoload (`autoload => true`).

This class serves as a reusable component for any AI service integration that relies on simple API key authentication, abstracting the common logic for adding the key to HTTP requests and defining its storage.
