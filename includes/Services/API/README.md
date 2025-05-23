# AI Services API

Directory: `includes/Services/API/`

This directory forms the core public-facing API layer for interacting with AI services within the plugin. It provides the necessary classes and utilities for developers to format requests, process responses, manage chat histories, and handle various data types and enumerations related to AI interactions.

It is CRITICAL that all the available methods defined in the PHP `Helpers` class are kept in sync with the JavaScript/TypeScript functions defined in the `helpers.ts` file in `src/ai/`.

## Purpose

The primary purpose of the code within this directory is to:

- Provide the public-facing API, designed to be a comprehensive toolkit for developers, offering:
    - **Data Structures**: Standardized classes for representing AI-related data (see `Types/`).
    - **Enumerations**: Predefined sets of constants for common AI parameters (see `Enums/`).
    - **Helper Utilities**: Static methods for common tasks like data conversion and stream processing.
    - **Stream Processing**: Tools for handling streamed AI responses.
    - **History Management**: Mechanisms for persisting and retrieving chat histories.

## Key Components

### 1. `Helpers.php`

-   **Purpose**: The `final` class `Helpers` provides a collection of static utility methods to simplify common tasks when working with the AI Services API.
-   **Key Functionalities**:
    -   **Content Conversion**:
        -   `text_to_content()`: Converts a plain text string into a `Content` object.
        -   `text_and_attachment_to_content()` / `text_and_attachments_to_content()`: Creates multimodal `Content` objects from text and one or more WordPress attachments (handling file reading and base64 encoding).
        -   `content_to_text()`: Extracts combined textual content from a `Content` object.
        -   `get_text_from_contents()` / `get_text_content_from_contents()`: Retrieves text or the `Content` object itself from an array of `Content` instances.
        -   `get_candidate_contents()`: Extracts `Content` objects from a `Candidates` collection.
    -   **Stream Processing**:
        -   `process_candidates_stream()`: A factory method that returns a new `Candidates_Stream_Processor` instance for a given candidates generator.
    -   **History Persistence**:
        -   `history_persistence()`: Provides a singleton instance of `History_Persistence` for managing chat histories.
    -   **Data URL and Blob Manipulation**:
        -   `file_to_base64_data_url()`: Converts a file path/URL to a base64 data URL.
        -   `file_to_blob()`: Converts a file path/URL to a `Blob` object.
        -   `blob_to_base64_data_url()`: Converts a `Blob` object to a base64 data URL.
        -   `base64_data_url_to_blob()`: Converts a base64 data URL back to a `Blob` object.
        -   `base64_data_to_base64_data_url()` / `base64_data_url_to_base64_data()`: Utility methods for ensuring correct base64 data URL formatting.
-   **Technical Decisions**:
    -   All methods are `static` for easy access without needing to instantiate the `Helpers` class.
    -   Relies on other API components like `Formatter` (from `Services/Util/`), `Content_Role` (enum), and various `Types` classes.
    -   Integrates with WordPress functions for attachment handling (`get_attached_file`, `wp_check_filetype`, etc.).

### 2. `Candidates_Stream_Processor.php`

-   **Purpose**: This `final` class, `Candidates_Stream_Processor`, is responsible for processing a stream of AI response candidates. When AI models stream responses (e.g., for text generation), they often send data in chunks. This processor aggregates these chunks into a complete `Candidates` object.
-   **Architecture**:
    -   It takes a `Generator<Candidates>` in its constructor, which yields individual `Candidates` chunks.
    -   The `read_all(callable $chunk_callback = null): Candidates` method iterates through the generator, accumulating the chunks. An optional callback can be provided to act on each chunk as it arrives (e.g., for displaying text incrementally).
    -   The `add_chunk(Candidates $candidates): void` method merges a new chunk of candidates with the existing accumulated candidates. It intelligently appends text content from corresponding parts.
    -   `get_complete(): ?Candidates` returns the fully assembled `Candidates` object, but only if the generator has finished yielding all chunks.
-   **Technical Decisions**:
    -   Designed to handle real-time aggregation of streamed data.
    -   The merging logic in `add_chunk` and `append_content` ensures that partial text responses are correctly combined.

### 3. `History_Persistence.php`

-   **Purpose**: The `History_Persistence` class is responsible for saving, loading, and clearing chat histories. It uses WordPress user meta as the storage backend.
-   **Architecture**:
    -   Depends on `Current_User` and `Meta_Repository` (from `wp-oop-plugin-lib`) for WordPress integration.
    -   **Key Methods**:
        -   `has_history(string $feature, string $slug)`: Checks if a history exists.
        -   `load_history(string $feature, string $slug)`: Loads a specific `History` object.
        -   `save_history(History $history)`: Saves a `History` object, updating the last modified time.
        -   `clear_history(string $feature, string $slug)`: Deletes a specific history.
        -   `load_histories_for_feature(string $feature)`: Loads all histories associated with a particular feature.
    -   **Internal Mechanics**:
        -   Manages a list of history keys (feature and slug combinations) in a separate user meta entry (`ais_history_keys`).
        -   Each history is stored as a separate user meta entry, keyed by `ais_history__{feature}__{slug}`.
        -   Keys are prefixed with the current site's database prefix (`$wpdb->get_blog_prefix()`) to ensure uniqueness in multisite environments.
        -   Includes validation for feature and slug identifiers (`is_valid_identifier()`).
        -   Uses an internal cache (`$history_slugs`) for the list of available history keys to reduce database queries.
-   **Technical Decisions**:
    -   Leverages WordPress user meta for per-user history storage.
    -   Ensures multisite compatibility by prefixing meta keys.
    -   Provides a structured way to organize histories by "feature" and a unique "slug" within that feature.

## Subdirectories

### `Enums/`

-   **Purpose**: This subdirectory contains PHP classes that simulate enumerations (enums). These enums define fixed sets of named constants used throughout the AI Services API to represent possible values for parameters like content roles (`Content_Role`), AI capabilities (`AI_Capability`), and service types (`Service_Type`).
-   **Relevance**: Provides type-safe and well-defined constant values, improving code clarity and reducing errors from magic strings.
-   *(For more details, see `includes/Services/API/Enums/README.md`)*

### `Types/`

-   **Purpose**: This subdirectory houses PHP classes representing various data structures (Data Transfer Objects, Value Objects, Configuration Objects, and Collections) used in the API. Examples include `Content`, `Candidate`, `History`, `Image_Generation_Config`, and `Text_Generation_Config`.
-   **Relevance**: These classes ensure data consistency, provide clear contracts for API interactions, and often include serialization/deserialization methods (`from_array`, `to_array`) and JSON schema definitions.
-   *(For more details, see `includes/Services/API/Types/README.md`)*

This API layer, along with its `Enums` and `Types`, provides a robust and developer-friendly interface for integrating AI functionalities into WordPress.
