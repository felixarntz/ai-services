[Back to overview](./README.md)

# Enabling the Assistant Chatbot Feature

There is a simple WordPress Assistant chatbot available as an experimental feature of the plugin, effectively acting as a proof of concept. Since the plugin is purely an infrastructure plugin that other plugins can use to access AI capabilities in WordPress, that chatbot feature is disabled by default.

If you want to test or use the chatbot, you can easily enable it via filter:

```php
add_filter( 'ai_services_chatbot_enabled', '__return_true' );
```
