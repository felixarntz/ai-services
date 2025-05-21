# AI Services WP-CLI Commands

Directory: `includes/Services/CLI/`

This directory contains the WP-CLI command implementations for the AI Services plugin. It allows users to interact with and manage AI services and models directly from the command line.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a command-line interface (CLI) for managing and utilizing AI services registered within the plugin.
- Enable listing of available AI services and their details.
- Allow users to list models available for specific AI services.
- Facilitate text generation using configured AI models via CLI commands.
- Facilitate image generation using configured AI models via CLI commands.
- Offer various options for formatting output and filtering results for CLI users.

## Architecture and Technical Decisions

The core of this directory is the `AI_Services_Command` class, which registers several WP-CLI subcommands:

- `wp ai-services list`: Lists all registered AI services, with options to filter by slug, availability, and format output.
- `wp ai-services get <service>`: Displays detailed information about a specific AI service.
- `wp ai-services list-models <service>`: Lists all available models for a given, configured AI service.
- `wp ai-services generate-text [...] <prompt>`: Generates text content using a specified (or automatically selected) service and model. It supports multimodal input via attachments and function calling.
- `wp ai-services generate-image [...] <prompt>`: Generates image content using a specified (or automatically selected) service and model.

Key technical aspects include:

- **Dependency Injection**: The `AI_Services_Command` class receives its dependencies, such as `Services_API`, `Current_User`, and `Capability_Controller`, via its constructor. This promotes loose coupling and testability. The `Services_API` is central for interacting with the core services logic, available in `includes/Services/Services_API.php`.
- **WP-CLI Integration**: The class leverages `WP_CLI` and `WP_CLI\Formatter` for command registration, argument parsing, and output formatting (e.g., table, JSON, CSV).
- **Service and Model Interaction**: It uses the `Services_API` to fetch service information (via `Service_Entity` from `includes/Services/Entities/`) and model metadata. For generation tasks, it retrieves `Generative_AI_Model` instances that conform to contracts like `With_Text_Generation` or `With_Image_Generation` found in `includes/Services/Contracts/`.
- **Configuration and Types**: The command utilizes various configuration types and enums from `includes/Services/API/Types/` (e.g., `Text_Generation_Config`, `Image_Generation_Config`) and `includes/Services/API/Enums/` (e.g., `AI_Capability`).
- **Capability Management**: It respects WordPress capabilities for accessing AI services. However, it includes a mechanism (`maybe_bypass_cap_requirements`) to bypass these checks when commands are run in a typical WP-CLI context (i.e., without a specific WordPress user session), ensuring full access for administrative CLI operations. This logic interacts with the `Capability_Controller` from the `wp-oop-plugin-lib` dependency.
- **Error Handling**: Uses `WP_CLI::error()` for reporting issues and `Generative_AI_Exception` (from `includes/Services/Exception/`) for handling AI-specific errors.
- **Streaming Support**: For text generation, it supports streaming responses to provide immediate feedback for long-running tasks, if enabled and supported by the model.
- **Multimodal and Function Calling**: The `generate-text` command supports passing attachment IDs for multimodal input and function declarations for AI models that support function calling, leveraging helpers and types from `includes/Services/API/`.
