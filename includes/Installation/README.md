# Installation Functionality

Directory: `includes/Installation/`

This directory contains the necessary code for managing the plugin's installation, upgrade, and uninstallation processes. It ensures that the plugin is set up correctly and that data is handled appropriately during these lifecycle events.

## Purpose

The primary purpose of the code within this directory is to:

- Define the actions to be taken when the plugin is first installed.
- Handle data upgrades when the plugin is updated from an older version to a newer one.
- Clean up plugin-specific data from the database when the plugin is uninstalled, if the user opts to remove all data.

## Key Components

-   **`Plugin_Installer.php`**: This file contains the `Plugin_Installer` class, which extends the `Abstract_Installer` class from the `wp-oop-plugin-lib` (located in `third-party/Felix_Arntz/WP_OOP_Plugin_Lib/Installation/`). It implements the core logic for installation, upgrades, and uninstallation.

## Technical Decisions

-   **Installation (`install_data` method)**: Currently, no specific data installation steps are performed when the plugin is activated for the first time.
-   **Upgrade (`upgrade_data` method)**: When upgrading, the plugin checks the previous version. If an upgrade from a version prior to `n.e.x.t` is detected, it invalidates all service request caches. This is necessary because the return shape of the `list_models()` method changed, and cached data would be stale. The cache invalidation logic uses the `Service_Request_Cache` class found in `includes/Services/Cache/`.
-   **Uninstallation (`uninstall_data` method)**: Upon uninstallation (and if the user has opted to delete all data), the plugin performs direct database queries to:
    -   Delete all WordPress options that start with the `ais_` prefix.
    -   Delete all user metadata (site-specific) that starts with the `[blog_prefix]ais_` prefix.
    Direct database queries are deemed acceptable here as they are executed only once during the uninstallation process.
