# Main Interface Module

Directory: `src/interface/`

This directory serves as the primary module for the plugin's user interface, using the familiar layout and design patterns from the WordPress block editor (Gutenberg). It aggregates and exports the core UI components and state management logic necessary for the plugin's frontend. The actual implementation of components and state is delegated to its subdirectories.

## Purpose

The primary purpose of the code within this directory is to:

- Act as a central entry point for all user interface related functionalities of the plugin.
- Organize the interface-specific code by separating UI components from their state management logic into dedicated subdirectories.
- Re-export modules from its subdirectories, `src/interface/components/` and `src/interface/store/`, to provide a cohesive API for the rest of the application.

## Structure

The `src/interface/` directory itself contains minimal direct logic. Its main file, `index.ts`, primarily exports all modules from its two subdirectories:

-   **`src/interface/components/`**: Contains React components that define the structure and interactive elements of the plugin's user interface. For more details, see the `README.md` in `src/interface/components/`.
-   **`src/interface/store/`**: Contains the Redux store configurations for managing the state of the UI elements. For more details, see the `README.md` in `src/interface/store/`.

This structure promotes a clear separation of concerns, making the interface codebase more maintainable and easier to navigate.
