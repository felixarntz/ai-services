# Utility Functions and Types

Directory: `src/utils/`

This directory contains a collection of general-purpose utility functions used across the frontend codebase. These utilities provide common functionalities such as handling errors or processing data streams.

## Purpose

The primary purpose of the code within this directory is to:

- Provide reusable helper functions that encapsulate common logic, promoting code reuse and reducing redundancy.
- Offer utilities for robust error handling and logging.
- Facilitate the processing of streaming data from server responses.

## Key Components

### Error Handling

- **`error-to-string.ts`**: Provides a function `errorToString` that converts various error types (including `Error` objects and other objects with a `message` property) into a consistent string representation suitable for display or logging.
- **`log-error.ts`**: Exports a `logError` function that uses `errorToString` to format an error and then logs it to the browser console via `console.error`.

### Stream Processing

- **`process-stream.ts`**: Includes the `processStream` function, which handles `Response` objects with streaming bodies (specifically `text/event-stream` containing JSON-encoded chunks). It decodes the stream and provides an `AsyncGenerator` that yields parsed data chunks. This is crucial for features that receive data incrementally, such as live updates or AI model responses.

These utilities form a foundational layer for various parts of the plugin's frontend, ensuring consistency and simplifying common development tasks.
