# Chatbot Functionality

Directory: `includes/Chatbot/`

This directory contains the PHP classes responsible for implementing the AI-powered chatbot feature within the AI Services plugin. The code handles the backend logic for the chatbot, including its initialization, asset management, AI configuration, and conditional loading. It integrates with the WordPress environment to provide a contextual and helpful chat experience.

## Purpose

The primary purpose of the code within this directory is to:

- Implement an AI-powered chatbot feature that can be enabled on a WordPress site.
- Manage the registration, enqueuing, and rendering of assets required for the chatbot's user interface (UI), which is likely handled by JavaScript in the `src/chatbot/` directory.
- Configure the AI model with detailed system instructions and dynamic contextual information about the WordPress installation (e.g., site version, active theme, plugins, user role).
- Provide a mechanism to conditionally load and activate the chatbot feature, ensuring it only runs when explicitly enabled and when compatible AI services are configured via the `includes/Services/` directory.
- Integrate seamlessly with WordPress hooks and APIs, utilizing the `wp-oop-plugin-lib` for object-oriented interactions.

## Key Components

The core components include the `Chatbot` class, which manages the chatbot's presence on the frontend and admin areas, the `Chatbot_AI` class, which crafts the system instructions and gathers WordPress-specific context for the AI, and the `Chatbot_Loader` class, which determines whether the chatbot feature should be activated based on plugin settings and the availability of required AI service capabilities.

## Architecture and Technical Decisions

- **Modular Design**: The chatbot functionality is organized into distinct classes with specific responsibilities:
    - `Chatbot.php`: Handles WordPress integration, asset management (scripts from `build/chatbot/`), UI rendering (the root `<div>` for the frontend application), and filtering AI model parameters.
    - `Chatbot_AI.php`: Focuses on generating the comprehensive system prompt for the AI, incorporating details about the WordPress environment to tailor the chatbot's responses.
    - `Chatbot_Loader.php`: Manages the conditional loading of the chatbot, checking an `ai_services_chatbot_enabled` filter and the availability of AI services with `TEXT_GENERATION` and `CHAT_HISTORY` capabilities (defined in `includes/Services/API/Enums/`).
- **Dependency Injection**: Classes are designed to receive their dependencies (e.g., `Plugin_Env`, `Site_Env`, `Services_API` from `includes/Services/Services_API.php`, `Script_Registry`, `Style_Registry`) through their constructors, promoting testability and maintainability.
- **Conditional Feature Activation**: The chatbot is an opt-in feature. `Chatbot_Loader::can_load()` ensures that the feature is only active if the `ai_services_chatbot_enabled` filter returns `true` and the necessary AI capabilities are supported by at least one configured AI service.
- **Contextual AI Prompts**: `Chatbot_AI::get_system_instruction()` dynamically constructs a detailed system prompt. This prompt guides the AI's behavior and provides it with relevant information about the WordPress site, such as version, active theme, plugins, user roles, and multisite status, to enhance the quality and relevance of its responses.
- **Asset Handling**: The `Chatbot` class registers and enqueues JavaScript and CSS assets located in the `build/chatbot/` directory, which are the compiled outputs from the source files likely in `src/chatbot/`.
- **WordPress Best Practices**: The code adheres to WordPress development standards, using hooks for integration and relying on the `wp-oop-plugin-lib` (prefixed under `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\`) for object-oriented wrappers around core WordPress functionalities.
- **REST API Integration**: The `Chatbot::filter_model_params()` method suggests that the chatbot interacts with AI services through a common REST API endpoint, modifying the parameters specifically for the chatbot feature.
