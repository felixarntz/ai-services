# Utility Functions and Types

Directory: `src/utils/`

This directory contains a collection of general-purpose utility functions and TypeScript type definitions used across the frontend codebase. These utilities provide common functionalities such as combining store configurations, handling errors, processing data streams, and defining shared types for state management.

## Purpose

The primary purpose of the code within this directory is to:

- Provide reusable helper functions that encapsulate common logic, promoting code reuse and reducing redundancy.
- Define core TypeScript types and interfaces, particularly for state management using a Redux-like pattern (e.g., with `@wordpress/data`).
- Offer utilities for robust error handling and logging.
- Facilitate the processing of streaming data from server responses.

## Key Components

### State Management Utilities

- **`combine-store-configs.ts`**: Exports a function `combineStoreConfigs` designed to merge multiple store configuration objects into a single configuration. This is particularly useful for modularizing store definitions. It ensures type safety and checks for duplicate keys across different parts of the store (initialState, actions, selectors, etc.). It relies on types defined in `store-types.ts`.
- **`store-types.ts`**: Contains essential TypeScript type definitions and interfaces (e.g., `StoreConfig`, `Action`, `ActionCreator`, `Selector`, `Resolver`) for creating and managing data stores, compatible with `@wordpress/data` or similar state management libraries.

### Error Handling

- **`error-to-string.ts`**: Provides a function `errorToString` that converts various error types (including `Error` objects and other objects with a `message` property) into a consistent string representation suitable for display or logging.
- **`log-error.ts`**: Exports a `logError` function that uses `errorToString` to format an error and then logs it to the browser console via `console.error`.

### Stream Processing

- **`process-stream.ts`**: Includes the `processStream` function, which handles `Response` objects with streaming bodies (specifically `text/event-stream` containing JSON-encoded chunks). It decodes the stream and provides an `AsyncGenerator` that yields parsed data chunks. This is crucial for features that receive data incrementally, such as live updates or AI model responses.

These utilities form a foundational layer for various parts of the plugin's frontend, ensuring consistency and simplifying common development tasks.
