[Back to overview](./README.md)

# Disabling the Assistant Chatbot Feature

The WordPress Assistant chatbot is the single user-facing built-in feature the plugin comes with, effectively acting as a proof of concept. Other than that, the plugin is purely an infrastructure plugin that other plugins can use to access AI capabilities in WordPress.

If you want to get rid of the chatbot, you can easily disable it via filter:

```php
add_filter( 'ai_services_chatbot_enabled', '__return_false' );
```
