# AI Services Admin Interface

Directory: `includes/Services/Admin/`

## Overview

This directory contains PHP classes responsible for creating and managing the WordPress admin-facing user interfaces for the AI Services plugin. These interfaces allow users to configure the plugin settings, manage AI service providers, and interact with the AI Playground.

## Purpose

The primary purpose of the code within this directory is to:

- Registering and rendering the main "AI Services" settings page.
- Registering and rendering the "AI Playground" page.
- Implementing WordPress Admin Pointers (contextual tooltips) to guide users towards these pages.
- Defining reusable links to these admin pages, typically used for plugin action links.

## Key Components

The main classes in this directory are:

-   **`Settings_Page.php` (`Settings_Page`)**:
    -   Defines the main admin page for configuring AI services and API keys.
    -   Extends `Abstract_Admin_Page` from the `wp-oop-plugin-lib`.
    -   Enqueues JavaScript and CSS assets for the settings interface (likely built with TypeScript/React, sourced from `src/services-page/`).
    -   Preloads necessary data from the REST API to ensure a responsive user experience.
    -   Requires the `ais_manage_services` capability for access.

-   **`Playground_Page.php` (`Playground_Page`)**:
    -   Defines the admin page for the AI Playground, allowing users to experiment with configured AI models and capabilities.
    -   Extends `Abstract_Admin_Page`.
    -   Enqueues assets for the Playground interface (likely built with TypeScript/React, sourced from `src/playground-page/`), including CodeMirror for syntax highlighting.
    -   Preloads REST API data, including service configurations and chat histories.
    -   Requires the `ais_use_playground` capability for access.

-   **`Settings_Page_Link.php` (`Settings_Page_Link`)**:
    -   Extends `Admin_Page_Link` from `wp-oop-plugin-lib`.
    -   Represents a reusable link to the "AI Services" settings page, providing the label "Settings". This is typically used in the plugin's entry on the WordPress Plugins page.

-   **`Settings_Page_Pointer.php` (`Settings_Page_Pointer`)**:
    -   Extends `Abstract_Admin_Page_Link_Pointer` from `wp-oop-plugin-lib`.
    -   Implements a WordPress Admin Pointer that directs users to the "AI Services" settings page.
    -   This pointer is conditionally displayed, primarily when no AI services have been configured yet, encouraging initial setup. It depends on the `Services_API` (from `includes/Services/Services_API.php`) to check this condition.

-   **`Playground_Page_Pointer.php` (`Playground_Page_Pointer`)**:
    -   Extends `Abstract_Admin_Page_Link_Pointer`.
    -   Implements a WordPress Admin Pointer that encourages users to explore the "AI Playground".
    -   This pointer is conditionally displayed, typically after AI services have been configured. It also depends on the `Services_API`.

## Architectural Decisions and Patterns

-   **Abstraction and Reusability**: Classes leverage base classes from the `wp-oop-plugin-lib` (e.g., `Abstract_Admin_Page`, `Abstract_Admin_Page_Link_Pointer`, `Admin_Page_Link`), promoting a consistent and object-oriented approach to building admin interfaces. These library classes are included via `third-party/` and prefixed.
-   **Dependency Injection**: Constructors utilize dependency injection for core WordPress services (`Script_Registry`, `Style_Registry`) and plugin-specific services (`Services_API`, `Admin_Page_Link`), enhancing testability and decoupling.
-   **Frontend Integration**: The admin pages (`Settings_Page`, `Playground_Page`) serve as shells that primarily render a root `<div>` element. The dynamic user interface is then mounted into these elements by JavaScript (TypeScript/React) applications. This separation allows for a modern, app-like experience within the WordPress admin.
-   **REST API Preloading**: To improve initial page load performance and user experience, both admin pages preload essential data using `rest_preload_api_request` and the `wp.apiFetch.createPreloadingMiddleware`. This makes data immediately available to the frontend components.
-   **Conditional UI Elements**: Admin Pointers are displayed conditionally based on the plugin's configuration state (e.g., whether services are set up), providing timely guidance to users.
-   **WordPress Capabilities**: Access to admin pages is controlled by custom WordPress capabilities (`ais_manage_services`, `ais_use_playground`), ensuring proper access control.
-   **Customized Admin Experience**:
    -   The `remove-screen-spacing` CSS class is added to the `admin_body_class`, suggesting these pages might utilize a more full-width or custom layout.
    -   Standard WordPress admin notices are removed on these pages to provide a cleaner interface, focusing on the plugin's specific UI.

## Dependencies and Interactions

-   **`wp-oop-plugin-lib`**: Core architectural foundation for admin pages and links, used via prefixed versions located in `third-party/`.
-   **`includes/Services/Services_API.php`**: The `Services_API` class is crucial for determining the state of AI service configurations, which influences the display of Admin Pointers.
-   **`src/services-page/` and `src/playground-page/`**: These directories contain the TypeScript/React source code for the frontend interfaces rendered within the admin pages defined here.
-   **WordPress REST API**: The admin pages interact with custom REST API endpoints (e.g., `/ai-services/v1/...`) for data retrieval and persistence.
