/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

const actions = {
	/**
	 * Sets a preference.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string} name  Preference name.
	 * @param {any}    value Preference value.
	 * @return {Function} Action creator.
	 */
	setPreference( name, value ) {
		return ( { registry } ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services', name, value );
		};
	},

	/**
	 * Toggles a preference.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string} name Preference name.
	 * @return {Function} Action creator.
	 */
	togglePreference( name ) {
		return ( { registry, select } ) => {
			const currentValue = select.getPreference( name );
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services', name, ! currentValue );
		};
	},
};

const selectors = {
	getPreference: createRegistrySelector( ( select ) => ( state, name ) => {
		return select( preferencesStore ).get( 'ai-services', name );
	} ),
};

const storeConfig = {
	actions,
	selectors,
};

export default storeConfig;
