# Interface Components

Directory: `src/interface/components/`

This directory contains various React components that form the core user interface structure of the application. These components handle the overall layout, navigation, and interactive elements like modals, sidebars, and notifications.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a consistent and extensible interface framework for the application.
- Manage the main application layout, including header, footer, and sidebar regions.
- Offer reusable UI components for common interface patterns such as modals, notices, and snackbars.
- Handle keyboard shortcuts and accessibility features for navigating the interface.
- Integrate with the WordPress components and data stores for a seamless user experience.

## Key Components

- **`App/`**: Contains the root `App` component which sets up the main structure of the application, including `SlotFillProvider` and `ErrorBoundary`. It wraps the main `Interface` component and registers global components like `ShortcutsRegister` and `KeyboardShortcutsHelpModal`.
- **`Interface/`**: The `Interface` component is the main layout component, utilizing `InterfaceSkeleton` from `@wordpress/interface`. It manages the display of header, footer, sidebar, notices, and snackbars. It also handles distraction-free mode and navigation regions.
- **`Header/`**, **`HeaderActions/`**, **`Footer/`**, **`Sidebar/`**: These components define slot-fill areas for the main layout regions of the application. They allow other parts of the application to inject content into these standard locations.
    - `HeaderActions/` specifically provides a slot for actions within the main header.
    - `Sidebar/` uses `@wordpress/interface`'s `ComplementaryArea` to manage sidebars, allowing them to be pinnable and activated.
- **`Modal/`**: Provides a `Modal` component that uses `@wordpress/components`' `Modal` for displaying modal dialogs. It integrates with the `interfaceStore` (likely located in `src/interface/store/`) to manage modal visibility.
- **`Notices/`** and **`Snackbars/`**: These components are responsible for displaying different types of notifications to the user.
    - `Notices/` displays standard, potentially dismissible, notices.
    - `Snackbars/` displays temporary, auto-hiding snackbar notifications. Both integrate with the `@wordpress/notices` store.
- **`KeyboardShortcutsHelpModal/`** and **`KeyboardShortcutsMenuItem/`**: These components work together to provide users with a way to view available keyboard shortcuts.
    - `KeyboardShortcutsHelpModal/` displays the actual modal with a list of shortcuts.
    - `KeyboardShortcutsMenuItem/` provides a menu item that can be used to trigger this modal.
- **`ShortcutsRegister/`**: This component is responsible for registering global keyboard shortcuts for the application using the `@wordpress/keyboard-shortcuts` store.
- **`DistractionFreePreferenceToggleMenuItem/`**: Provides a menu item to toggle the "distraction-free" mode, which typically hides secondary UI elements to help users focus. It interacts with preferences stored likely via `@wordpress/preferences`.
- **`PinnedSidebars/`**: This component renders a slot for pinned sidebar items, allowing users to quickly access frequently used sidebars. It uses `PinnedItems.Slot` from `@wordpress/interface`.
- **`index.ts`**: This file serves as the main entry point for the `src/interface/components/` directory, exporting all the key components for use elsewhere in the application.

## Architectural Considerations

- **SlotFill Pattern**: The interface heavily relies on the SlotFill pattern provided by `@wordpress/components`. This allows for a decoupled architecture where different parts of the application can contribute to common UI areas (like Header, Footer, Sidebar) without direct dependencies.
- **WordPress Integration**: Components are built to integrate closely with WordPress core packages, such as `@wordpress/components`, `@wordpress/data`, `@wordpress/keyboard-shortcuts`, `@wordpress/interface`, and `@wordpress/notices`.
- **State Management**: The interface components interact with a central data store, likely located in `src/interface/store/`, for managing UI state such as active modals, sidebars, and user preferences like distraction-free mode.
- **Accessibility**: Emphasis is placed on accessibility through keyboard navigation (region navigation, shortcuts) and ARIA attributes.
- **Reusability**: Many components are designed to be reusable and configurable, forming a foundational UI library for the application.

For data storage and state management related to the interface, refer to `src/interface/store/`.
For more general, application-agnostic reusable components, refer to `src/components/`.
