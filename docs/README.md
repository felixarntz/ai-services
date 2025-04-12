# AI Services

The AI Services plugin makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

| AI Services: Settings screen | AI Services: Playground screen |
| ------------- | ------------- |
| ![The AI Services settings screen where users can paste their AI service credentials](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-1.png)  | ![Multimodal text generation in the AI Playground where users can explore the different AI model capabilities](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-2.png)  |

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for an AI Playground screen to explore AI capabilities as well as a settings screen to configure AI service credentials. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

[Read the documentation to learn how to use the AI Services plugin.](./Documentation.md)
