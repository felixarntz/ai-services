# Reusable UI Components

Directory: `src/components/`

This directory contains a collection of reusable React components designed for use throughout the AI Services plugin's frontend interfaces. These components encapsulate common UI patterns and functionalities, promoting consistency and reducing code duplication.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a library of standardized UI elements for building user interfaces within the plugin.
- Encapsulate complex UI logic into reusable and maintainable components.
- Ensure a consistent look and feel across different parts of the plugin's frontend.
- Offer specialized controls for common tasks, such as API key input, multi-selection, and sensitive data display.

## Key Components

The `src/components/` directory houses several key subdirectories, each containing a specific UI component or a set of related components:

-   **`ApiKeyControl/`**: Provides a specialized text input field for API keys, integrating with the `SensitiveTextControl/` to allow toggling the visibility of the key. See `src/components/ApiKeyControl/` for more details.
-   **`FieldsetBaseControl/`**: Offers a base component for creating fieldset elements, often used to group related form controls. See `src/components/FieldsetBaseControl/` for more details.
-   **`HelpText/`**: A simple component for rendering help or instructional text, typically associated with form controls. See `src/components/HelpText/` for more details.
-   **`MultiCheckboxControl/`**: Implements a control that allows users to select multiple options from a list of checkboxes. It can optionally include a search filter provided by `OptionsFilterSearchControl/`. See `src/components/MultiCheckboxControl/` for more details.
-   **`OptionsFilterSearchControl/`**: A search input component designed to filter a list of options, commonly used in conjunction with components like `MultiCheckboxControl/`. See `src/components/OptionsFilterSearchControl/` for more details.
-   **`Parts/`**: Contains components for rendering various types of content "parts", such as text (Markdown), media (images, audio, video), and structured data (JSON for function calls/responses). This is crucial for displaying diverse AI model outputs. See `src/components/Parts/` for more details.
-   **`PluginIcon/`**: A component that renders the AI Services plugin's SVG icon. See `src/components/PluginIcon/` for more details.
-   **`SensitiveTextControl/`**: A text input component that masks its content by default (like a password field) and provides a button to toggle visibility. This is used by `ApiKeyControl/`. See `src/components/SensitiveTextControl/` for more details.
-   **`Tabs/`**: Provides a set of components (`Tabs`, `Tabs.TabList`, `Tabs.Tab`, `Tabs.TabPanel`) for creating tabbed interfaces, leveraging the Ariakit library for accessibility and functionality. See `src/components/Tabs/` for more details.

All components are exported via the `src/components/index.ts` file, making them easily importable from a central location.

## Architectural Notes

-   Most components are functional React components utilizing TypeScript for type safety.
-   Styling is typically handled via SCSS files co-located within each component's subdirectory.
-   WordPress core components and utilities (e.g., from `@wordpress/components`, `@wordpress/element`, `@wordpress/i18n`) are extensively used.
-   Accessibility is a key consideration, with components aiming to follow ARIA best practices, sometimes facilitated by libraries like Ariakit (as seen in `Tabs/`).
-   Components are designed to be relatively self-contained, promoting reusability across different parts of the plugin, such as the AI Playground (`src/playground-page/`) and the Services settings page (`src/services-page/`).
