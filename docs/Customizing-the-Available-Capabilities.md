---
title: Customizing the Available Capabilities
layout: page
---

The AI Services plugin comes with several custom user capabilities which are granted to users based on certain existing capabilities by default. This behavior can be modified to fine tune which users on a WordPress site are able to use the AI capabilities.

## Available capabilities

Here is a list of the available user capabilities, what they are for, and how they are granted by default:

* `ais_manage_services`: Base capability which controls which users can access the _Settings > AI Services_ screen and configure AI service credentials.
    * By default, all users with the `manage_options` WordPress Core capability (typically users with the administrator role) are granted this capability.
    * Example check: `current_user_can( 'ais_manage_services' )`
* `ais_access_services`: Base capability which controls which users can use AI features using any of the configured AI services.
    * By default, all users with the `edit_posts` WordPress Core capability (typically users with the contributor role or higher) are granted this capability.
    * Example check: `current_user_can( 'ais_access_services' )`
* `ais_access_service`: Meta capability which controls which users can use AI features using a _specific_ configured AI service.
    * By default, any user with the `ais_access_services` base capability will also be granted this meta capability.
    * Example check: `current_user_can( 'ais_access_service', 'google' )`
* `ais_use_playground`: Meta capability which controls which users can access and use the _Tools > AI Playground_ screen.
    * By default, any user with the `ais_access_services` base capability will also be granted this meta capability.
    * Example check: `current_user_can( 'ais_use_playground' )`

## How to customize the default behavior

The AI Services plugin's `ai_services_load_services_capabilities` action hook can be used to customize the way that these capabilities are granted by default. It receives an instance of a capability controller class which exposes methods to change the behavior.

Below are some examples of how this action hook could be used:

### Altering which WordPress Core capabilities the plugin capabilities are granted based on

You can alter which WordPress Core capability a user needs to have to be granted one of the plugin's capabilities. For example, you could use the following snippet to "bump" the requirement for accessing AI services to `manage_options` which would mean that (typically) only administrators would be able to access any AI features based on these services.

```php
add_action(
	'ai_services_load_services_capabilities',
	function ( $capability_controller ) {
		$capability_controller->grant_cap_for_base_caps(
			'ais_access_services',
			array( 'manage_options' )
		);
	}
);
```

### Not granting any of the plugin capabilities based on existing WordPress Core capabilities

You can set the required WordPress Core capabilities for all base capabilities of the AI Services plugin to an empty array, to prevent them from being granted based on other capabilities altogether. Note that if you do that you will need to implement another mechanism to grant the capabilities yourself - otherwise no user will be able to use the AI Services plugin.

The following snippet would disable granting the plugin capabilities based on any WordPress Core capabilities:

```php
add_action(
	'ai_services_load_services_capabilities',
	function ( $capability_controller ) {
		$capability_controller->grant_cap_for_base_caps( 'ais_access_services', array() );
		$capability_controller->grant_cap_for_base_caps( 'ais_manage_services', array() );
	}
);
```

### Altering for a specific AI service which users can access it

Instead of modifying the required WordPress Core capability to access _all_ AI services, you could also more granularly alter the conditions required to access a _specific_ AI service. This is done by providing a custom callback function to resolve the `ais_access_service` meta capability.

Here is an example which would lead to the `openai` AI service to be only accessible to users with both the `ais_access_services` capability and the `manage_options` capability (typically administrators), while the other AI services would continue to be accessible to anyone with the `ais_access_services` capability.

```php
add_action(
	'ai_services_load_services_capabilities',
	function ( $capability_controller ) {
		$capability_controller->set_meta_map_callback(
			'ais_access_service',
			function ( int $user_id, string $service_slug ) {
				$required_base_caps = array( 'ais_access_services' );
				if ( 'openai' === $service_slug ) {
					$required_base_caps[] = 'manage_options';
				}
				return $required_base_caps;
			}
		);
	}
);
```

### Disabling access to the AI Playground

Other than customizing which users are granted a certain capability, you can also decide to not grant a capability to any users, effectively disabling the underlying feature.

For example, the following snippet would prevent any user from accessing and using the AI Playground administration screen, by using the special WordPress Core capability `do_not_allow`:

```php
add_action(
	'ai_services_load_services_capabilities',
	function ( $capability_controller ) {
		$capability_controller->set_meta_map_callback(
			'ais_use_playground',
			function () {
				return array( 'do_not_allow' );
			}
		);
	}
);
```
