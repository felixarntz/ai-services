# Interface State Management

Directory: `src/interface/store/`

This directory contains the Redux store configurations for managing the state of various user interface elements within the AI Services plugin. It centralizes state logic for components like sidebars, modals, panels, and user preferences, leveraging WordPress core data stores and a custom combined store structure.

## Purpose

The primary purpose of the code within this directory is to:

- Define and manage the state for different parts of the plugin's user interface.
- Provide actions and selectors for interacting with UI elements such as sidebars, modals, and panels.
- Handle user-specific preferences related to the interface.
- Consolidate individual store configurations into a single, unified Redux store for the plugin interface, identified by the name `ai-services/interface` (defined in `src/interface/store/name.ts`).
- Integrate with WordPress core data stores, specifically `@wordpress/interface` for modal and sidebar management, and `@wordpress/preferences` for panel and general preference management.

## Architecture

The main store is initialized in `src/interface/store/index.ts`, which combines several smaller store configurations:
- `src/interface/store/sidebar.ts`: Manages the state of sidebars, including which sidebar is active and the default sidebar.
- `src/interface/store/modal.ts`: Handles the state of modals, allowing them to be opened, closed, and toggled.
- `src/interface/store/panel.ts`: Manages the active state of collapsible panels within the interface.
- `src/interface/store/preferences.ts`: Provides a way to set, get, and toggle user-specific preferences.

These individual store configurations are combined using the `combineStoreConfigs` utility found in `src/utils/combine-store-configs.ts`. The type definitions for store configurations, actions, and thunks, such as `StoreConfig`, are typically sourced from `src/utils/store-types.ts`.

Each store module (e.g., `modal.ts`, `panel.ts`) defines its own actions and selectors, often acting as a wrapper or an extension around functionalities provided by WordPress core stores like `@wordpress/interface` and `@wordpress/preferences`. The `sidebar.ts` module also includes its own reducer to manage specific state like `defaultSidebarId`.
