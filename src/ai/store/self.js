/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';

const RECEIVE_PLUGIN_DATA = 'RECEIVE_PLUGIN_DATA';

const initialState = {
	pluginData: undefined,
};

const actions = {
	/**
	 * Receives plugin data from the server.
	 *
	 * @since 0.4.0
	 *
	 * @param {Object} pluginData Plugin data received from the server, as key value pairs.
	 * @return {Function} Action creator.
	 */
	receivePluginData( pluginData ) {
		return ( { dispatch } ) => {
			dispatch( {
				type: RECEIVE_PLUGIN_DATA,
				payload: {
					pluginData,
				},
			} );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.4.0
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 * @return {Object} New state.
 */
function reducer( state = initialState, action ) {
	switch ( action.type ) {
		case RECEIVE_PLUGIN_DATA: {
			const { pluginData } = action.payload;
			return {
				...state,
				pluginData,
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Fetches the plugin data from the server.
	 *
	 * @since 0.4.0
	 *
	 * @return {Function} Action creator.
	 */
	getPluginData() {
		return async ( { dispatch } ) => {
			const pluginData = await apiFetch( {
				path: '/ai-services/v1/self',
			} );
			dispatch.receivePluginData( pluginData );
		};
	},
};

const selectors = {
	getPluginData: ( state ) => {
		return state.pluginData;
	},

	getPluginSlug: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_slug;
	} ),

	getPluginBasename: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_basename;
	} ),

	getPluginVersion: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_version;
	} ),

	getPluginHomepageUrl: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_homepage_url;
	} ),

	getPluginSupportUrl: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_support_url;
	} ),

	getPluginContributingUrl: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_contributing_url;
	} ),

	getPluginSettingsUrl: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_settings_url;
	} ),

	getPluginPlaygroundUrl: createRegistrySelector( ( select ) => () => {
		const pluginData = select( STORE_NAME ).getPluginData();
		return pluginData?.plugin_playground_url;
	} ),

	currentUserCan: createRegistrySelector(
		( select ) =>
			( state, capability, ...args ) => {
				const pluginData = select( STORE_NAME ).getPluginData();
				if ( ! pluginData ) {
					return undefined;
				}
				if ( args.length > 0 ) {
					capability = `${ capability }::${ args.join( '::' ) }`;
				}
				return (
					pluginData.current_user_capabilities[ capability ] || false
				);
			}
	),
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
