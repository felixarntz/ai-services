# Plugin Settings and Services Data Store

Directory: `src/settings/`

This directory contains the TypeScript code for the Redux data store (`@wordpress/data`) responsible for managing plugin-wide settings and information about available AI services in the context of editing settings. It centralizes state management for settings retrieved from and saved to the WordPress REST API, as well as details about AI services registered with the plugin.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a centralized Redux store, identified as `ai-services/settings`, for managing plugin settings and AI service data.
- Manage the state of general plugin settings, such as API keys and data handling preferences. This includes fetching these settings from the `/wp/v2/settings` REST API endpoint and persisting changes back to it.
- Manage the state of AI service configurations, including fetching details for individual services and lists of all available services from the `/ai-services/v1/services` REST API endpoint.
- Offer a comprehensive set of actions, selectors, and resolvers for interacting with this data from various frontend components throughout the plugin, particularly within the settings pages and the AI Playground.
- Facilitate dynamic updates, such as refreshing service details when an associated API key is modified.

## Key Components

-   **`index.ts`**: This is the entry point for the data store. It combines the individual store configurations for services and settings using the `combineStoreConfigs` utility from `src/utils/` and registers the unified store with `@wordpress/data`.
-   **`name.ts`**: Defines the unique constant `STORE_NAME` (`ai-services/settings`) used to identify this Redux store within the WordPress data registry.
-   **`services.ts`**: This file implements the store module dedicated to AI services. It handles:
    -   Fetching and storing a list of all available AI services (`ServiceResource` type from `src/ai/types.ts`).
    -   Fetching and storing details for individual AI services.
    -   Actions like `receiveServices`, `receiveService`, and `refreshService`.
    -   Selectors like `getServices` and `getService`.
-   **`settings.ts`**: This file implements the store module for general plugin settings. It manages:
    -   Fetching plugin settings from the WordPress REST API (`/wp/v2/settings`).
    -   Saving modified settings back to the REST API.
    -   Handling local state for modified settings before they are saved.
    -   Specific actions for setting values, including API keys (`setApiKey`) and data deletion preferences (`setDeleteData`).
    -   Selectors for retrieving individual settings, checking for modifications, and determining saveability status.
    -   Interaction with `@wordpress/notices` for user feedback upon saving settings.

## Technical Decisions

-   **State Management with `@wordpress/data`**: The WordPress data module (`@wordpress/data`) is used for a Redux-like state management pattern, integrating well with the WordPress admin environment.
-   **Modular Store Design**: The store is split into logical modules (`services.ts` and `settings.ts`) for better organization and maintainability, combined into a single store in `index.ts`.
-   **REST API Integration**: Settings and service information are primarily sourced from and persisted to WordPress REST API endpoints. `apiFetch` from `@wordpress/api-fetch` is used for these communications.
-   **CamelCase Convention for Local Setting Names**: WordPress options often use snake_case (e.g., `ais_option_name`). Within this store, these are converted to camelCase (e.g., `optionName`) for consistency with JavaScript/TypeScript conventions. The `settings.ts` module handles this mapping.
-   **Utility Functions**: Common functionalities like error logging (`logError`) and combining store configurations are abstracted into utility functions located in the `src/utils/` directory.
