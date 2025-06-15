# Services Page Application

Directory: `src/services-page/`

This directory contains the entry point for the AI Services settings page, which is displayed in the WordPress admin area. It is responsible for initializing and rendering the main React application that constitutes the settings interface.

## Purpose

The primary purpose of the code within this directory is to:

- Serve as the main initializer for the AI Services settings page.
- Mount the root React component (`SettingsApp`) into the DOM.
- Bridge the WordPress environment with the React-based settings interface.

The main logic for rendering the settings page is handled by the `SettingsApp` component, which is located in the `src/services-page/components/SettingsApp/` directory. This `index.tsx` file ensures that the application is loaded and displayed correctly when the user navigates to the AI Services settings page.

For the actual components that make up the settings page UI, refer to the `src/services-page/components/` directory. The data and state management for the settings themselves are primarily handled by stores and utilities found in the `src/settings/` directory.
