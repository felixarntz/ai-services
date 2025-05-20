# Architecture and Design Decisions

This file outlines the key architectural patterns and design decisions made in the AI Services WordPress plugin.

## Overall Architecture

The AI Services WordPress plugin is built using a combination of PHP and TypeScript. PHP is primarily used for the backend logic, WordPress integration, and handling AI service interactions, while TypeScript is used for the frontend components and interfaces, such as the AI Playground.

The project follows a modular structure, with code organized into logical directories.

## Directory Structure Overview

Here is an overview of the top-level directories and their general purpose:

- `.clinerules/`: Contains custom rules and context files for the Cline tool.
- `.git/`: Git version control files.
- `.github/`: GitHub Actions workflows and other GitHub repository configurations.
- `.wordpress-org/`: Assets for the WordPress.org plugin directory.
- `build/`: Compiled production assets (JavaScript, CSS, etc.).
- `build-types/`: TypeScript build output for type declarations.
- `docs/`: Project documentation files.
- `examples/`: Example code demonstrating how to use the plugin.
- `includes/`: Contains the majority of the PHP backend code.
    - `Anthropic/`: Code related to the Anthropic AI service integration.
    - `Chatbot/`: Code for the chatbot feature.
    - `Google/`: Code related to the Google AI service integration.
    - `Installation/`: Code for plugin installation and activation.
    - `OpenAI/`: Code related to the OpenAI AI service integration.
    - `Services/`: Core AI services logic, including API handling, authentication, caching, and admin interfaces.
    - `vendor/`: Composer dependencies.
- `node_modules/`: Node.js dependencies.
- `src/`: Contains the TypeScript source code for frontend assets.
    - `@types/`: TypeScript type definitions.
    - `ai/`: Core AI-related TypeScript code.
    - `chatbot/`: TypeScript code for the chatbot frontend.
    - `components/`: Reusable frontend components.
    - `interface/`: TypeScript code for the main plugin interface.
    - `playground-page/`: TypeScript code for the AI Playground admin page.
    - `services-page/`: TypeScript code for the services settings page.
    - `settings/`: TypeScript code for plugin settings.
    - `utils/`: Utility functions and helpers in TypeScript.
- `tests/`: Project tests.
    - `phpunit`: PHPUnit tests.
- `third-party/`: Third-party PHP libraries bundled and prefixed by PHP-Scoper (original source found in `vendor/`).
- `tools/`: Development tools and scripts.
- `vendor/`: Composer dependencies.

## Key Design Patterns

### PHP specific

- The overall PHP namespace is `Felix_Arntz\AI_Services`.
- The project uses OOP best practices including dependency injection.
- The project relies on a [WordPress specific PHP package `wp-oop-plugin-lib`](https://github.com/felixarntz/wp-oop-plugin-lib), which provides object oriented wrappers for WordPress Core APIs.
    - All classes, interfaces, and traits from this package are bundled in this project's codebase, within the `third-party/` directory. They are prefixed (via PHP-Scoper) to avoid conflicts with other plugins using them.
        - Example: Instead of `Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Container`, use `Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Container`.
    - These object oriented wrappers MUST be used instead of directly calling WordPress Core functions.
        - The only exceptions are translation functions (e.g. `__()`, `_x()`) and escaping functions (e.g. `esc_html()`, `wp_kses()`).
