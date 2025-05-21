# Service Decorators

Directory: `includes/Services/Decorators/`

This directory contains classes that utilize the decorator pattern to wrap and extend the functionality of core AI service implementations. These decorators act as a form of middleware, allowing for common features such as caching, parameter validation, and filtering to be applied centrally to any AI service that conforms to the `Generative_AI_Service` interface.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a flexible way to add common functionalities (cross-cutting concerns) to different AI service implementations without altering their original code.
- Implement caching mechanisms for frequently accessed, and potentially slow, service methods like `is_connected()` and `list_models()`, leveraging the `Service_Request_Cache` from `includes/Services/Cache/`.
- Enable centralized validation of parameters passed to service methods, such as those in `get_model()`.
- Offer a hook (`ai_services_model_params`) for developers to filter and modify model parameters before a model instance is retrieved.
- Ensure that all decorated services still adhere to the `Generative_AI_Service` contract defined in `includes/Services/Contracts/`.
