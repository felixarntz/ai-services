# AI Core Client-Side Functionality

Directory: `src/ai/`

This directory serves as the central hub for all client-side (TypeScript) AI functionalities within the AI Services plugin. It brings together core classes for interacting with AI models, comprehensive type definitions for data structures, enumerations for standardized values, helper functions for common AI-related tasks, utility functions for data manipulation and validation, and the Redux data store for managing AI-related state.

It is CRITICAL that all the available JavaScript/TypeScript functions defined in the `helpers.ts` file are kept in sync with the methods defined in the PHP `Helpers` class in `includes/Services/API/`.

## Purpose

The primary purpose of the code within this directory is to:

-   Provide a comprehensive client-side toolkit for developers to integrate and interact with AI capabilities.
-   Define the fundamental data structures and TypeScript types (`types.ts`) used for AI requests, responses, configurations, and model parameters.
-   Offer a standardized set of enumerations (see `src/ai/enums/README.md`) for key AI concepts such as capabilities (e.g., text generation, image generation), content roles (e.g., user, model), and service types.
-   Supply a collection of helper functions (`helpers.ts`) for common operations. These include converting between text and structured `Content` objects, processing streamed AI responses, managing history persistence, and handling file-to-Base64 conversions for multimodal inputs.
-   Include utility functions (`util.ts`) for formatting new AI content, validating content and model parameters, detecting requested capabilities from content, and finding appropriate models based on specified parameters.
-   House the core TypeScript classes (see `src/ai/classes/README.md`) that abstract interactions with AI services and models. This includes support for generic AI models, browser-native AI capabilities, and managing multi-turn chat sessions.
-   Manage all client-side AI-related state using a Redux data store (see `src/ai/store/README.md`). This store handles the availability of AI services, chat session data, and general plugin metadata relevant to AI features.
-   Act as the primary export point (`index.ts`) for all public AI-related modules, enums, helpers, and the data store, providing a unified interface for other parts of the application.

## Structure and Key Components

The `src/ai/` directory is organized into several key files and subdirectories:

-   **`classes/`**: Contains the core TypeScript classes for AI interactions. For more details, see `src/ai/classes/README.md`.
-   **`enums/`**: Defines enumerations for standardized AI-related values. For more details, see `src/ai/enums/README.md`.
-   **`store/`**: Contains the Redux data store configuration for managing AI state. For more details, see `src/ai/store/README.md`.
-   **`helpers.ts`**: Provides a suite of helper functions to simplify common AI-related tasks, such as data conversion for `Content` objects, stream processing, and history persistence. It leverages classes like `CandidatesStreamProcessor` and `HistoryPersistence` from the `src/ai/classes/` directory.
-   **`index.ts`**: Serves as the main entry point for the `src/ai/` module, re-exporting key functionalities from `enums`, `helpers`, and `store` to be consumed by other parts of the application.
-   **`types.ts`**: Defines all TypeScript types and interfaces used across the AI functionalities. This includes types for `Content`, `Part`, `Candidate`, `ChatSessionOptions`, `ModelParams`, `ServiceResource`, and more, ensuring type safety and clarity.
-   **`util.ts`**: Contains utility functions for input validation (e.g., `validateContent`, `validateModelParams`), content formatting (`formatNewContent`), capability detection (`detectRequestedCapabilitiesFromContent`), and model selection (`findModel`).

Together, these components provide a robust and well-structured foundation for building AI-powered features on the client-side within the WordPress environment.
