[Back to overview](./README.md)

# Accessing AI Services with WP-CLI

This section provides some documentation on how to access AI services through the command line with WP-CLI.

The canonical entry point to all of the AI Services commands is the `wp ai-services` command namespace. The concrete usage is best outlined by examples. For illustrative purposes, here is a full example of generating text content using the `google` service:

```bash
wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature --user=admin
```

This assumes you have a administrator account with username "admin" on your site. Any commands related to an available service (i.e. fully configured and connected) can only be run by users with sufficient permissions. The global WP-CLI argument `--user` can be used to specify the WordPress site account to use.

For more specific examples with explanations, see the following sections.

## Available commands

The following WP-CLI commands are available:

* `wp ai-services list`: Lists the registered AI services.
* `wp ai-services get`: Gets details about a registered AI service.
* `wp ai-services list-models`: Lists the models for a registered and available AI service.
* `wp ai-services generate-text`: Generates text content using a generative model from an available AI service.

Use `wp help ai-services` or `wp help ai-services <command>` to get detailed usage instructions and lists of the available arguments for each command.

## Generating text content using an AI service

The following examples cover the `wp ai-services generate-text` command.

### Using a specific AI service

You can provide the slug of a specific AI service as first positional argument to the `wp ai-services generate-text` command, for example the `google` service:

```bash
wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature --user=admin
```

Note that this command will return an error if the service is not available (i.e. configured by the user with valid credentials). Therefore it is recommended to first check whether the service available, for example using the `wp ai-services get` command:

```bash
if [ "$(wp ai-services get google --field=is_available --user=admin)" == "true" ]; then
  wp ai-services generate-text google "What can I do with WordPress?" --feature=my-test-feature --user=admin
else
  echo "The google service is not available."
fi
```

### Using a specific AI model

If you want to go more granular and also specify which exact model to use from the service, you can specify the model slug after the service slug in the `wp ai-services generate-text` command. The following example specifies to use the `gemini-1.5-pro` model from the `google` service:

```bash
wp ai-services generate-text google gemini-1.5-pro "What can I do with WordPress?" --feature=my-test-feature --user=admin
```

### Using any available AI service

For many AI use-cases, relying on different AI services may be feasible. For example, to respond to a simple text prompt, you could use _any_ AI service that supports text generation. If so, it is advised to not require usage of a _specific_ AI service, so that the end user can configure whichever service they prefer and still use the relevant command. You can do so by simply omitting both the service and model positional arguments from the `wp ai-services generate-text` command:

```bash
wp ai-services generate-text "What can I do with WordPress?" --feature=my-test-feature --user=admin
```

This command will automatically choose whichever service and model with text generation capabilities is available. It will only return an error if no capable service is configured at all.
