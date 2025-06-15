# AI Playground Page

Directory: `src/playground-page/`

This directory contains the TypeScript code responsible for rendering and managing the AI Playground admin page within the WordPress interface. It includes the main application entry point (`index.tsx`), type definitions specific to the playground (`types.ts`), and subdirectories for the user interface components (`components/`) and the data store (`store/`).

## Purpose

The primary purpose of the code within this directory is to:

- Initialize and mount the main React application for the AI Playground, making it available to users in the WordPress admin area.
- Define TypeScript types and interfaces that are used throughout the playground's components and data store, ensuring type safety and clarity. These types often build upon or relate to core types defined in `src/ai/types.ts`.
- Structure the AI Playground's user interface through a collection of React components, detailed further in `src/playground-page/components/README.md`.
- Manage the state of the AI Playground, including AI service selection, model configuration, available AI capabilities, and the history of messages exchanged with AI models. This state management is handled by a Redux-like data store, as described in `src/playground-page/store/README.md`.

## Architecture

The `index.tsx` file serves as the entry point for the AI Playground page. It utilizes `@wordpress/dom-ready` to ensure the DOM is fully loaded before attempting to render the main `PlaygroundApp` React component (located in `src/playground-page/components/`). This file also imports from `./store` to initialize and register the playground's data store.

The `types.ts` file centralizes type definitions crucial for the playground's functionality, such as `AiPlaygroundMessage`, `AiServiceOption`, `AiModelOption`, and `AiCapabilityOption`. These types ensure consistency and provide clear contracts for data structures passed between components and the store.

The overall functionality of the AI Playground is a collaborative effort between the components in `src/playground-page/components/` (which handle the UI and user interactions) and the data store in `src/playground-page/store/` (which manages application state). Reusable UI elements may also be sourced from `src/components/` and `src/interface/components/`.
