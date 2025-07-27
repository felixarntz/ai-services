# AI Client-Side Classes

Directory: `src/ai/classes/`

This directory contains the core TypeScript classes that form the client-side interface for interacting with AI services and models within the AI Services plugin. These classes handle communication with the backend API, manage AI model instances, process streaming data, facilitate chat sessions, and provide mechanisms for history persistence. They also include specialized classes for browser-based AI interactions.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a structured, object-oriented way to interact with AI services and models from the client-side (JavaScript/TypeScript).
- Abstract the complexities of direct API calls to the backend, offering a cleaner interface for developers using the plugin's AI capabilities.
- Support various AI operations such as text generation, image generation, and streaming responses.
- Enable multi-turn chat sessions with integrated history management.
- Offer a mechanism for persisting and retrieving interaction histories (e.g., chat logs) through the backend.
- Include specialized implementations for leveraging browser-native AI capabilities when available.

## Key Classes and Their Roles

The main classes in this directory and their responsibilities are:

-   **`GenerativeAiService.ts`**:
    This is the base class representing an AI service provider (e.g., OpenAI, Anthropic, Google). It is responsible for listing available models within that service and providing instances of `GenerativeAiModel` configured for specific tasks or features. It acts as the entry point for accessing models of a particular service. It also provides access to service metadata.

-   **`GenerativeAiModel.ts`**:
    This class represents a specific AI model from a service. It handles the actual API calls to the backend for operations like text generation (`generateText`, `streamGenerateText`), image generation (`generateImage`), and text to speech (`textToSpeech`). It encapsulates model-specific parameters and capabilities.

-   **`BrowserGenerativeAiService.ts` & `BrowserGenerativeAiModel.ts`**:
    These are specialized versions of `GenerativeAiService` and `GenerativeAiModel`, respectively. They are designed for interacting with AI models that run directly in the user's browser, leveraging browser-native APIs (e.g., `window.LanguageModel`). `BrowserGenerativeAiModel` adapts the browser's API to conform to the plugin's standard `Candidates` response structure.

-   **`ChatSession.ts`**:
    This class manages interactive, multi-turn conversations with an AI model. It maintains the chat history and provides methods (`sendMessage`, `streamSendMessage`) to send new messages and receive responses, automatically updating the history.

-   **`CandidatesStreamProcessor.ts`**:
    A utility class for processing and aggregating streamed AI responses. AI models often return responses in chunks (streaming); this class helps in collecting these chunks and combining them into a complete response, with an option for a callback on each received chunk.

-   **`HistoryPersistence.ts`**:
    This class provides an interface for saving, loading, and managing interaction histories (like chat logs) via the backend API. It allows features to persist user interactions and retrieve them later, using unique feature and slug identifiers.

## Interactions and Dependencies

-   **Backend API**: These classes primarily interact with the backend REST API endpoints defined under `/ai-services/v1/`. For example, `GenerativeAiModel` makes calls to endpoints like `/ai-services/v1/services/{serviceSlug}:generate-text`. `HistoryPersistence` interacts with `/ai-services/v1/features/{feature}/histories/{slug}`.
-   **`@wordpress/api-fetch`**: This WordPress package is used for making HTTP requests to the backend API.
-   **Local Types and Enums**: Type definitions are typically imported from `../types.ts` (see `src/ai/types.ts`) and enumerations from `../enums/` (see `src/ai/enums/`).
-   **Utility Functions**: Helper functions are often imported from `../util.ts` (see `src/ai/util.ts`) and `../../utils/` (see `src/utils/`).

## Architectural Notes

-   **Abstraction**: The classes follow an abstraction pattern where `GenerativeAiService` acts as a factory or provider for `GenerativeAiModel` instances. This decouples the client code from the specifics of model instantiation.
-   **Specialization via Inheritance**: Browser-specific AI interactions are handled by `BrowserGenerativeAiService` and `BrowserGenerativeAiModel`, which extend their generic counterparts.
-   **Streaming**: Asynchronous generators (`AsyncGenerator`) are used extensively for handling streaming responses from AI models, allowing for real-time data processing.
-   **Error Handling**: Errors from API calls (via `apiFetch`) are generally caught and re-thrown as standard `Error` objects, often with the message from the API response.
-   **Content and Capabilities**: The system uses a `Content` structure for passing prompts and history, and `ModelParams` (including `capabilities`) to specify requirements for model selection and operation. Validation of capabilities against model metadata is performed before actions.
