# AI Services: AI Client Plugin for WordPress

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

| AI Services: Settings screen | AI Services: Playground screen |
| ------------- | ------------- |
| ![The AI Services settings screen where users can paste their AI service credentials](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-1.png)  | ![Multimodal text generation in the AI Playground where users can explore the different AI model capabilities](https://raw.githubusercontent.com/felixarntz/ai-services/refs/heads/main/.wordpress-org/screenshot-2.png)  |

[Try inside WordPress Playground](https://playground.wordpress.net/?mode=seamless#{%20%22$schema%22:%20%22https://playground.wordpress.net/blueprint-schema.json%22,%20%22meta%22:%20{%20%22title%22:%20%22AI%20Services%20Playground%22,%20%22author%22:%20%22felixarntz%22%20},%20%22preferredVersions%22:%20{%20%22php%22:%20%228.2%22,%20%22wp%22:%20%22latest%22%20},%20%22plugins%22:%20[%20%22ai-services%22%20],%20%22features%22:%20{%20%22networking%22:%20true%20},%20%22login%22:%20true,%20%22landingPage%22:%20%22/wp-admin/%22%20})

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for an AI Playground screen to explore AI capabilities as well as a settings screen to configure AI service credentials. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

**Disclaimer:** The AI Services plugin is still in its early stages, with a limited feature set and more being added. A crucial part of refining the plugin is shaping the APIs to make them easy to use and cover the different generative AI capabilities that the AI services offer in a uniform way. That's why your feedback is much appreciated!

## Documentation

[Please see the project website for a more detailed introduction and comprehensive documentation.](https://felixarntz.github.io/ai-services/)

## Installation and usage

You can install [the latest built release from the WordPress plugin directory](https://wordpress.org/plugins/ai-services/), which in the long term will be the recommended way to use the plugin. Keep in mind that any `0.x.y` releases are considered early access and may contain breaking changes.

Alternatively, especially in this early development stage of the plugin, feel free to test the plugin by cloning the GitHub repository. Afterwards, please run the following commands to make sure the dependencies are installed and the plugin build is complete:

```sh
git clone https://github.com/felixarntz/ai-services.git wp-content/plugins/ai-services
cd wp-content/plugins/ai-services
composer install
npm install
npm run build
```

If you want to test the plugin in its own built-in development environment, please follow the instructions in the [code contributing guidelines](./CONTRIBUTING.md#getting-started-with-writing-code).

Once the AI Services plugin is installed and activated, you can configure the plugin with your AI service credentials using the _Settings > AI Services_ screen in the WP Admin menu.

## Using the plugin

Once the plugin is active, you will find a new _Settings > AI Services_ submenu in the WordPress administration menu. In there, you can configure your AI service API keys. After that, you can use the _Tools > AI Playground_ screen to explore the available AI capabilities of the different connected services.

If you have enabled the WordPress assistant chatbot via filter, you should see a small "Need help?" button in the lower right throughout WP Admin after you have configured at least one (valid) API key.

Please refer to the [plugin documentation](https://felixarntz.github.io/ai-services/Documentation.html) for instructions on how you can actually use the AI capabilities of the plugin in your own projects.

## License

This plugin is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](/LICENSE) for complete license.
