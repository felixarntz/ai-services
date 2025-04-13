---
title: Rendering AI API Key Controls in Your Own UI
layout: page
---

Out of the box, the AI Services plugin allows managing API keys for all registered AI services on its own screen _Settings > AI Services_. Thanks to the plugin's central AI infrastructure, **users will only have to configure the AI API once**.

If you are developing a plugin that relies on the AI Services infrastructure, you may however want to additionally show the API key controls elsewhere, for example on your own plugin's settings screen. This makes it easy for an end user to find all settings related to your plugin in one place.

For this purpose, the AI Services plugin provides the necessary utilities for you to show relevant AI service API key controls wherever you like.

Depending on your plugin's UI tech stack, you may want to consider different approaches.

## Using a React based administration screen

If the administration screen where you want to include API key controls follows latest WordPress Admin UI best practices and relies on React, you can simply reuse the UI component that the AI Services plugin itself uses on the _Settings > AI Services_ screen. The component `ApiKeyControl` is available via the `ais-components` asset handle in PHP, which refers to both a script and a stylesheet. When the asset is loaded, you can access the component in JavaScript from `aiServices.components.ApiKeyControl`.

To connect the component with the actual API key persistence layer, you can use the WordPress datastore `ai-services/settings`, which is available via the `ais-settings` script handle in PHP.

To use both the component and the store in your own React based administration screen, make sure to enqueue the necessary assets, either by adding them as dependencies to your own script and stylesheet respectively, or by explicitly enqueuing them manually on your screen's relevant action hook:

```php
wp_enqueue_script( 'ais-settings' );
wp_enqueue_script( 'ais-components' );
wp_enqueue_style( 'ais-components' );
```

### Example: A specific AI service's API key control

Below you see an example for how to use the `ApiKeyControl` component and wire it up to the datastore to display an API key control for the OpenAI API key:

```js
/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**
 * AI Services dependencies
 */
const { store: aiSettingsStore } = window.aiServices.settings;
const { ApiKeyControl } = window.aiServices.components;

function MyOpenAiApiKeyControl() {
	const service = useSelect( ( select ) =>
		select( aiSettingsStore ).getService( 'openai' )
	);
	const apiKey = useSelect( ( select ) =>
		select( aiSettingsStore ).getApiKey( 'openai' )
	);
	const { setApiKey } = useDispatch( aiSettingsStore );

	const onChangeApiKey = useCallback(
		( newApiKey ) => setApiKey( 'openai', newApiKey ),
		[ setApiKey ]
	);

	return (
		<ApiKeyControl
			service={ service }
			apiKey={ apiKey }
			onChangeApiKey={ onChangeApiKey }
		/>
	);
}
```

Important: This code example does not yet include actually _saving_ the API key. The `setApiKey` store action only updates the API key in the store state, but does not save it in the WordPress database. Please see the ["Saving API keys to the database" section](#saving-api-keys-to-the-database) on ways to save the value.

### Example: API key controls for all registered AI services

The below example goes a bit further than the one above: It shows how you can render API key controls for all registered AI services:

```js
/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**
 * AI Services dependencies
 */
const { store: aiSettingsStore } = window.aiServices.settings;
const { ApiKeyControl } = window.aiServices.components;

const EMPTY_ARRAY = [];

function MyApiKeyControlList() {
	const services = useSelect( ( select ) => {
		const { getServices, getApiKey } = select( aiSettingsStore );
		if ( getServices() === undefined ) { // Loading state.
			return EMPTY_ARRAY;
		}
		return Object.values( getServices() ).map( ( service ) => {
			return {
				...service,
				apiKey: getApiKey( service.slug ),
			};
		} );
	} );
	const { setApiKey } = useDispatch( aiSettingsStore );

	// The callback receives the service slug as second parameter.
	const onChangeApiKey = useCallback(
		( newApiKey, serviceSlug ) => setApiKey( serviceSlug, newApiKey ),
		[ setApiKey ]
	);

	return (
		<>
			{ services.map( ( { apiKey, ...service } ) => (
				<ApiKeyControl
					key={ service.slug }
					service={ service }
					apiKey={ apiKey }
					onChangeApiKey={ onChangeApiKey }
				/>
			) ) }
		</>
	);
}
```

Important: Just as the previous example, this code example does not yet include actually _saving_ the API key. Continue reading to learn more about how you can do that.

### Saving API keys to the database

As mentioned before, the `setApiKey` store action only updates the API key in the store state, but does not save it in the WordPress database. In order to save any API keys that were changed to the WordPress database, you need to use the `saveSettings` action from the same store.

If your administration screen has a "Save" button or similar explicit UI control to save settings, you would ideally call the `saveSettings` action as part of the callback that is triggered when the user clicks that button.

The `saveSettings` action is parameter-less and calling it is as simple as:

```js
const { saveSettings } = useDispatch( aiSettingsStore );

const onSave = () => {
	// ... (other save logic related to your screen)

	saveSettings();
};
```

Alternatively, if your administration screen saves any changes automatically (i.e. without the user having to click some kind of "Save" button), you could implement something similar for the API keys. You could modify the `onChangeApiKey` callback from the above examples to automatically save the change immediately. If you do that, make sure to debounce the `saveSettings()` call, to avoid excessive requests for every single character that may be changed in the input field.

Here is what that could look like:

```js
/*
 * `useDebounce` can be imported from `@wordpress/compose`.
 * Here it is used to only saved at most every 500 milliseconds.
 */
const debouncedSaveSettings = useDebounce( saveSettings, 500 );

const onChangeApiKey = useCallback(
	( newApiKey, serviceSlug ) => {
		setApiKey( serviceSlug, newApiKey );
		debouncedSaveSettings();
	},
	[ setApiKey ]
);
```

## Using a traditional PHP based administration screen

For a traditional PHP based administration screen using the [WordPress Settings API](https://developer.wordpress.org/plugins/settings/settings-api/), the AI Services plugin provides the [`API_Key_Control` class](https://github.com/felixarntz/ai-services/tree/main/includes/Services/API/Components/API_Key_Control.php) which renders relevant UI in PHP. It requires an instance of `Service_Entity` to access the service's metadata, and the current API key value which can be read via `get_option()`. You can either hard-code the underlying option name, or ideally retrieve it from the `Service_Entity` instance you create for the service.

Below you see an example for how to use the `API_Key_Control` class to display an API key control for the Google API key. In this example, it simply adds the field to WordPress's own _Settings > General_ screen.

```php
use Felix_Arntz\AI_Services\Services\Components\API_Key_Control;
use Felix_Arntz\AI_Services\Services\Entities\Service_Entity;

add_action(
	'admin_init',
	function () {
		// See below for why this global is included here.
		global $new_allowed_options;

		add_settings_section(
			'ais_api_keys',
			__( 'API Keys', 'ai-services' ),
			null,
			'general'
		);

		$service_entity = new Service_Entity( ai_services(), 'google' );

		$option_slugs = $service_entity->get_field_value( 'authentication_option_slugs' );
		foreach ( $option_slugs as $option_slug ) {
			/*
			 * This workaround is necessary since WordPress Core does not provide a function for this specifically.
			 * The `register_setting()` function is not suitable here, as the setting will already be registered in
			 * WordPress generally, so calling it again here just to assign it to the allowlist would cause problems.
			 */
			$new_allowed_options['general'][] = $option_slug;
		}

		$field_id      = 'ais-api-key-' . $service_entity->get_field_value( 'slug' );
		$field_control = new API_Key_Control(
			$service_entity,
			get_option( $option_slug ),
			array(
				'id_attr'   => $field_id,
				'name_attr' => $option_slug,
			)
		);
		add_settings_field(
			$field_id,
			$service_entity->get_field_value( 'name' ),
			array( $field_control, 'render_input' ),
			'general',
			'ais_api_keys',
			array( 'label_for' => $field_id )
		);
	}
);
```

In the example above, the `API_Key_Control` class's `render_input()` method is passed as a callback to `add_settings_field()`.

Alternatively, if your own administration screen uses PHP but does not use the WordPress Settings API, you can rely on the `API_Key_Control` class's `render()` method to render the entire control yourself, including wrapper element, label, input, and description.
