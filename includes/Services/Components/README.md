# Components

Directory: `includes/Services/Components/`

This directory houses reusable UI component classes for the AI Services plugin. These components are typically used to render specific parts of the plugin's administrative interfaces, ensuring a consistent look and feel for common UI elements related to service configuration.

## Purpose

The primary purpose of the code within this directory is to:

- Provide standardized UI components for rendering elements within the WordPress admin area.
- Encapsulate the logic and presentation for specific UI controls, such as API key input fields.
- Facilitate the creation of consistent and user-friendly interfaces for managing AI service settings.

## Key Components

### `API_Key_Control.php`

- **Purpose:** This class is responsible for rendering an API key input field. It handles the display of the input, its label, and descriptive text, including links to obtain or manage API keys.
- **Functionality:**
    - Displays a password input field for an API key.
    - Shows a label derived from the associated service's name.
    - Provides a description that indicates whether the API key is pre-configured (forced via a filter) or can be entered by the user.
    - Includes a link to the service provider's credentials page, if available.
- **Usage:** This control is typically used on settings pages where users need to configure API keys for different AI services. It integrates with `Service_Entity` objects from `includes/Services/Entities/` to fetch service-specific details and with `API_Key_Authentication` from `includes/Services/Authentication/` to determine default input attributes.
