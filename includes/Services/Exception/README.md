# AI Service Exceptions

Directory: `includes/Services/Exception/`

This directory contains custom exception classes used throughout the AI Services plugin, specifically for handling errors related to AI service operations.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a standardized way to represent and handle errors that occur during interactions with generative AI services.

## Architecture and Technical Decisions

- **`Generative_AI_Exception`**: This is the main exception class defined in this directory.
    - It extends the standard PHP `RuntimeException`. This choice indicates that these exceptions represent errors that occur during the execution of the program (e.g., an API request failing, unexpected response from an AI service).
    - It is used to signal issues specifically arising from generative AI service operations.

By having a dedicated exception class, consuming code can catch `Generative_AI_Exception` specifically, allowing for tailored error handling logic for AI-related issues, separate from other runtime errors that might occur in the plugin.
