# AI Playground Components

Directory: `src/playground-page/components/`

This directory contains all the React components that make up the AI Playground page. These components are responsible for rendering the user interface and handling user interactions within the playground.

## Purpose

The primary purpose of the code within this directory is to:

- Provide the overall structure and layout of the AI Playground page.
- Offer UI elements for configuring AI capabilities, selecting services and models, and adjusting model parameters.
- Display and manage the conversation flow, including user prompts and AI responses.
- Facilitate interaction with AI functionalities like function calling and viewing raw message data.
- Offer status updates and user feedback throughout the playground usage.

## Component Overview

The main components in this directory and their responsibilities are:

- **`PlaygroundApp/`**: This is the root component for the AI Playground. It orchestrates the overall layout, including the header, main content area, sidebar, and footer. It integrates various sub-components to build the complete playground interface. See `src/playground-page/components/PlaygroundApp/index.tsx`.
- **`PlaygroundMain/`**: This component renders the central part of the playground, which includes the system instruction input, the message display area, and the user input field. See `src/playground-page/components/PlaygroundMain/index.tsx`.
- **`PlaygroundCapabilitiesPanel/`**: A sidebar panel component that allows users to select the foundational and additional AI capabilities they want to use in the playground. See `src/playground-page/components/PlaygroundCapabilitiesPanel/index.tsx`.
- **`PlaygroundServiceModelPanel/`**: A sidebar panel component for selecting the AI service provider and the specific AI model to interact with. See `src/playground-page/components/PlaygroundServiceModelPanel/index.tsx`.
- **`PlaygroundModelConfigPanel/`**: A sidebar panel component that provides controls for configuring various parameters of the selected AI model, such as temperature, max output tokens, and aspect ratio for image generation. See `src/playground-page/components/PlaygroundModelConfigPanel/index.tsx`.
- **`PlaygroundFunctionDeclarationsPanel/`**: A sidebar panel component used when the 'Function Calling' capability is active. It allows users to select which predefined function declarations should be made available to the AI model. See `src/playground-page/components/PlaygroundFunctionDeclarationsPanel/index.tsx`.
- **`FunctionDeclarationsModal/`**: A modal component that allows users to manage (add, edit, delete) the function declarations available for AI function calling. See `src/playground-page/components/FunctionDeclarationsModal/index.tsx`.
- **`MessageCodeModal/`**: A modal component that displays the underlying code or raw JSON data for a selected message in the playground. This is useful for developers to inspect the data being sent to and received from the AI. See `src/playground-page/components/MessageCodeModal/index.tsx`.
- **`PlaygroundMoreMenu/`**: A dropdown menu component located in the header, providing access to options like distraction-free mode, keyboard shortcuts, and links to settings or documentation. See `src/playground-page/components/PlaygroundMoreMenu/index.tsx`.
- **`PlaygroundStatus/`**: A component, typically in the footer, that displays status messages to the user, such as "Sending prompt...", "Received response...", or error messages. See `src/playground-page/components/PlaygroundStatus/index.tsx`.

These components work together, often interacting with the Zustand store located in `src/playground-page/store/`, to provide a dynamic and interactive AI experimentation environment. Reusable UI elements are sourced from `src/components/` and `src/interface/components/`.
