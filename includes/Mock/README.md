# Mock

Directory: `includes/Mock/`

This directory contains mock implementations of AI services and models for testing and development purposes. It provides a way to simulate AI responses without relying on external AI providers.

## Purpose

The primary purpose of the code within this directory is to:

-   Provide mock AI services and models for testing and development.
-   Simulate AI responses without relying on external AI providers.
-   Enable developers to test their code against different AI scenarios.

The `Mock_AI_Service.php` file defines a mock AI service that can be used to list available models and create model instances. The `Mock_AI_Text_Generation_Model.php` and `Mock_AI_Image_Generation_Model.php` files define mock AI models for text and image generation, respectively.

The `Contracts/` directory contains the `With_Mock_Results.php` interface, which defines a contract for classes that can return mock results. The `Traits/` directory contains the `With_Mock_Results_Trait.php` trait, which implements the `With_Mock_Results` interface.
