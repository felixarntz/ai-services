# AI Data Store

Directory: `src/ai/store/`

This directory contains the Redux data store configuration for AI-related functionalities within the plugin. It utilizes `@wordpress/data` to manage client-side state for AI services, chat sessions, and general plugin metadata. The store is structured modularly, defining actions, reducers, selectors, and resolvers to interact with and manage this data effectively.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a centralized Redux store (`ai-services/ai`) for managing all AI-related client-side state.
- Define actions for initiating AI operations, such as starting chat sessions, sending messages, and fetching AI service configurations or general plugin data.
- Implement reducers that handle state mutations based on dispatched actions, ensuring predictable state updates.
- Offer selectors for retrieving specific pieces of AI-related data (e.g., available services, chat history, plugin settings) from the store.
- Include resolvers for handling asynchronous operations, such as fetching data from the WordPress REST API or determining browser-based AI capabilities.
- Manage the lifecycle and state of different AI services, including a special client-side 'browser' AI service that leverages built-in browser AI features.
- Handle the state for chat sessions, including conversation history, configuration options, and loading indicators for ongoing interactions.
- Store and provide access to general plugin metadata (like version, URLs) and current user capabilities related to the plugin.

## Key Components and Functionality

The store is organized into several key files, each responsible for a specific aspect of AI data management:

-   **`index.ts`**: This is the main entry point for the AI data store. It imports individual store configurations (`self`, `services`, `chat`), combines them into a single store configuration using a utility from `src/utils/combine-store-configs.ts`, creates the Redux store instance, and registers it with WordPress's data system.
-   **`name.ts`**: Defines and exports the unique constant `STORE_NAME` (`ai-services/ai`), which is used to identify this data store throughout the application.
-   **`browser.ts`**: Contains logic specific to the client-side 'browser' AI service. It includes functions to detect if the user's browser has built-in AI capabilities (like Gemini Nano for Chrome or Phi-4-mini for Edge, via `window.ai` or `window.LanguageModel`) and formats this information as a standard `ServiceResource`.
-   **`chat.ts`**: Manages the state related to AI chat sessions. This includes actions to start a new chat, send messages (with support for streaming responses), revert messages, and reducers to update chat history and loading states. It interacts with `ChatSession` instances from `src/ai/classes/`.
-   **`self.ts`**: Handles fetching and storing general plugin-related data. This includes information like the plugin's slug, version, various URLs (homepage, support, settings, playground), and the current user's capabilities concerning the plugin. Data is typically fetched from the `/ai-services/v1/self` REST endpoint.
-   **`services.ts`**: Manages the state of all available AI services, both server-side and the client-side 'browser' service. It fetches service configurations from the `/ai-services/v1/services` REST endpoint, stores them, and provides selectors to retrieve service instances (wrapping them in `GenerativeAiService` or `BrowserGenerativeAiService` from `src/ai/classes/`), check their availability, and access their metadata.

## Architectural Considerations

-   **Redux Pattern with `@wordpress/data`**: The store leverages the `@wordpress/data` package, which is WordPress's standard implementation of the Redux pattern for state management in JavaScript applications. This provides a predictable and centralized way to manage complex application state.
-   **Modular Design**: The store is broken down into smaller, focused modules (`self`, `services`, `chat`). Each module has its own actions, reducers, selectors, and potentially resolvers. These are then combined into a single store, promoting separation of concerns and making the codebase easier to maintain and understand.
-   **Service Abstraction**: The `services.ts` module, in conjunction with classes from `src/ai/classes/`, abstracts the details of individual AI service providers, presenting a consistent interface to the rest of the application.
-   **Asynchronous Operations**: Resolvers are used to handle asynchronous operations like fetching data from REST API endpoints (`apiFetch`) or querying browser capabilities. This keeps action creators synchronous where possible and centralizes data fetching logic.
-   **Client-Side AI Integration**: The inclusion of `browser.ts` demonstrates a technical decision to support and integrate with emerging client-side AI capabilities directly within the browser, offering a lightweight AI option without server-side calls.
-   **Type Safety**: The codebase is written in TypeScript, with explicit type definitions for state, actions, and payloads (e.g., using `StoreConfig`, `Action`, `ThunkArgs` from `src/utils/store-types.ts` and specific types from `src/ai/types.ts`). This enhances code reliability and developer experience.

## Related Code

For a complete understanding of this data store, refer to the following related directories and files:

-   **`src/ai/classes/`**: Contains class definitions for `GenerativeAiService`, `BrowserGenerativeAiService`, and `ChatSession`, which are instantiated and managed by this store.
-   **`src/ai/enums/`**: Provides various enumerations, such as `AiCapability`, used throughout the store and AI functionalities.
-   **`src/ai/types.ts`**: Defines core TypeScript types and interfaces used within the AI module, including those for store state and payloads.
-   **`src/utils/store-types.ts`**: Contains generic TypeScript type definitions for creating Redux store configurations.
-   **`src/utils/combine-store-configs.ts`**: Provides the utility function used in `index.ts` to merge the different parts of the store configuration.
