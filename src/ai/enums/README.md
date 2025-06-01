# AI Enums

Directory: `src/ai/enums/`

This directory contains TypeScript modules that define various enumerations (enums) used throughout the AI-related functionalities of the plugin. These enums provide a set of predefined constants for specific types, such as AI capabilities, content roles in a conversation, and AI service types. Each enum module typically exports the constant values and utility functions to validate or retrieve these values. The `index.ts` file serves as a central export point for all enums in this directory, making them accessible via common namespaces.

It is CRITICAL that all the available JavaScript/TypeScript enums and their values defined here are kept in sync with the PHP enums in `includes/Services/API/Enums/`.

## Purpose

The primary purpose of the code within this directory is to:

- Provide standardized sets of constant values for AI-related concepts, ensuring consistency across the application.
- Enhance type safety by offering predefined values for specific categories, reducing the likelihood of errors from using arbitrary strings.
- Offer utility functions for working with these enum values, such as checking if a given value is valid or retrieving a list of all possible values for an enum.
- Centralize the definition of these core enumerations for easier maintenance and discoverability.

## Files

This directory includes the following key files:

-   **`ai-capability.ts`**: Defines constants representing various AI capabilities supported or recognized by the system. Examples include `TEXT_GENERATION`, `IMAGE_GENERATION`, `FUNCTION_CALLING`, etc. It also provides `isValidValue` and `getValues` helper functions.
-   **`content-role.ts`**: Defines constants for the different roles that content can have, particularly in conversational AI contexts. Common roles include `USER`, `MODEL`, and `SYSTEM`. It also provides `isValidValue` and `getValues` helper functions.
-   **`modality.ts`**: Defines constants for the different types of data modalities that can be used as input or output. Examples include `TEXT`, `IMAGE`, and `AUDIO`. It also provides `isValidValue` and `getValues` helper functions.
-   **`service-type.ts`**: Defines constants for categorizing AI services based on their operational nature, such as `CLOUD`, `SERVER`, or `CLIENT`. It also provides `isValidValue` and `getValues` helper functions.
-   **`index.ts`**: Serves as the public interface for this directory. It re-exports the constants and helper functions from the individual enum files (e.g., `AiCapability`, `ContentRole`, `Modality`, `ServiceType`), excluding internal implementation details like `_VALUE_MAP`. This allows other parts of the application to import these enums from a single, consistent path.

## Technical Decisions

-   **Module-based Enums**: Instead of using TypeScript's `enum` keyword, these are implemented as modules exporting string constants. This approach offers flexibility and avoids potential issues associated with numeric enums or tree-shaking.
-   **`_VALUE_MAP` for Validation**: Each enum module uses an internal `_VALUE_MAP` object (a simple key-value map where keys are enum values and values are `true`) to efficiently implement `isValidValue` and `getValues` functions. This map is not exported directly to keep the public API clean.
-   **Centralized Exports**: The `index.ts` file acts as a barrel file, re-exporting the public parts of each enum. This simplifies imports in other modules, as they can import all enums from `src/ai/enums/` rather than from individual files. It also destructures and re-exports to hide the internal `_VALUE_MAP` constants.

These enums are fundamental for the type definitions and logic within the AI functionalities, particularly in `src/ai/types.ts` and services interacting with AI models.
