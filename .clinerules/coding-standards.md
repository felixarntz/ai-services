# Coding Standards and Conventions

This file documents the coding standards and conventions used in the AI Services WordPress plugin.

## General Guidelines

- ALWAYS follow the WordPress Coding Standards, across all programming languages.
- Write code that is backward compatible with the PHP version and WordPress version outlined in the [`CONTRIBUTING.md` file](../CONTRIBUTING.md).
- ALWAYS use TypeScript instead of JavaScript, except for configuration files where applicable.
- ALWAYS ask the user for approval first (`ask_followup_question`) before trying to include any external project dependencies.
- ALWAYS use the WordPress translation functions around every user-facing string. Example: `__( 'User-facing text', 'ai-services' )`
- Follow best security practices for WordPress, including user roles, escaping, validation, and using nonces where appropriate.
- Consider performance in all your solutions. Implement transients and other caching strategies where appropriate.
- Prefer modern or more recent WordPress coding techniques to older ones. There are a lot of old, suboptimal examples in your dataset.
- Avoid writing overly long or complex functions or methods.

See the "Guidelines for contributing code" section in the [`CONTRIBUTING.md` file](../CONTRIBUTING.md) for additional reference.

### PHP specific

- Use a class based architecture, following OOP best practices, such as single responsibility principle, dependency inversion principle, separation of concerns, and DRY.
- Use dependency injection where possible.
- NEVER trigger side effects from a class constructor. Actions and filters should be added through separate methods.
- ALWAYS add all necessary `use` statements for classes, interfaces, and traits from other files.
    - Exception: If the file is within the same directory, do not add a `use` statement.

### TypeScript specific

- Use functional programming, with a few exceptions for where classes might make sense.
- ALWAYS ask the user for approval first (`ask_followup_question`) before creating a new class.
- ALWAYS ask the user for approval first (`ask_followup_question`) before introducing a new type.
    - The only exception to this are types for React component props.
- ALWAYS add all necessary `import` statements for classes, constants, functions, and types from other files.
- NEVER explicitly provide the return type for React component functions (e.g. `JSX.Element`). Leave it out. It's unnecessary.
- NEVER use function parameter destructuring. ALWAYS destructure objects in the function body.

## Naming Conventions

### PHP specific

- Use PSR-4 compliant names for classes, interfaces, and traits.
- Use **snake_case** for property names, function names, and method names (e.g. `do_something`).
- Use **Pascal_Snake_Case** for class names, interface names, and trait names (e.g. `My_Class`).
- ALWAYS use all caps for acronyms within names (e.g. use `Generative_AI_Service`, not `Generative_Ai_Service`).

### TypeScript specific

- Use **camelCase** for property names, function names, and method names (e.g. `doSomething`).
- Use **PascalCase** for class names, interface names, and trait names (e.g. `MyClass`).
- NEVER use all caps for acronyms within names (e.g. use `GenerativeAiService`, not `GenerativeAIService`).

## Formatting

- ALWAYS use tabs for indentation.
- Exception: Use spaces to indent within contents of a single line, e.g. to align columns between a multiline array or a multiline doc block.

## Documentation

- Add doc blocks for every class, function, method, or property.
- Include a `@since` annotation for every class, function, method or property.
- ALWAYS provide type declarations and descriptions for parameters and return values in doc blocks.
- NEVER include the return doc block annotation `@return void` in the doc block.
- Start every doc block for a function or a method with a third-person verb. Example: `Displays text.`
- Every doc, whether doc block, description, or inline comment, must end in a full stop.
- ALWAYS align `@param` documentation lines for a single function or method.

### PHP specific

- Use PHPStan compatible type annotations.

### TypeScript specific

- ALWAYS follow the TSDoc standard.
    - The only exception is you should still include `@since` annotations, even though they are not a standard TSDoc tag.
- ALWAYS use `@returns`. Do not use `@return`.
- NEVER document individual properties of an object. Only add a single line of documentation for each overarching parameter.

## Tooling

### PHP specific

- Use `composer lint` to check for PHP lint errors.
- Use `composer format` to automatically fix PHP lint errors that can be automatically fixed.

### TypeScript specific

- Use `npm run lint-js` to check for TypeScript lint errors.
- Use `npm run format-js` to automatically fix TypeScript lint errors that can be automatically fixed.

### CSS specific

- Use `npm run lint-css` to check for CSS lint errors.
- Use `npm run format-css` to automatically fix CSS lint errors that can be automatically fixed.
