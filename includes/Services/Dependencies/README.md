# Script and Style Dependencies

Directory: `includes/Services/Dependencies/`

This directory contains the necessary classes for managing and registering the plugin's frontend script and style dependencies. It ensures that all JavaScript and CSS assets required by the AI Services plugin are correctly enqueued within the WordPress environment, leveraging a structured approach for asset management.

## Purpose

The primary purpose of the code within this directory is to:

- Centralize the registration of all plugin-specific JavaScript scripts and CSS stylesheets.
- Utilize the `Script_Registry` and `Style_Registry` classes from the `wp-oop-plugin-lib` (prefixed and located in `third-party/Felix_Arntz/WP_OOP_Plugin_Lib/Dependencies/`) for a consistent and object-oriented way of handling WordPress asset registration.
- Define unique handles, source URLs (pointing to the `build/` directory), asset manifest file paths (e.g., `build/ai/index.asset.php`), and loading strategies (e.g., 'defer') for each script.
- Define unique handles, source URLs (pointing to the `build/` directory), local file paths, asset manifest file paths, and dependencies (e.g., `wp-components`) for each stylesheet.
- Ensure that frontend assets for various plugin features like the AI core functionalities (`ais-ai`), settings (`ais-settings`), reusable components (`ais-components`), main interface (`ais-interface`), services settings page (`ais-services-page`), and the AI Playground page (`ais-playground-page`) are properly registered and available when needed.
