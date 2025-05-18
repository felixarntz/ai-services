/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';

type PluginData = {
	plugin_slug: string;
	plugin_basename: string;
	plugin_version: string;
	plugin_homepage_url: string;
	plugin_support_url: string;
	plugin_contributing_url: string;
	plugin_settings_url: string;
	plugin_playground_url: string;
	current_user_capabilities: Record< string, boolean >;
};

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceivePluginData = 'RECEIVE_PLUGIN_DATA',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceivePluginDataAction = Action<
	ActionType.ReceivePluginData,
	{ pluginData: PluginData }
>;

export type CombinedAction = UnknownAction | ReceivePluginDataAction;

export type State = {
	pluginData: PluginData | undefined;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const initialState: State = {
	pluginData: undefined,
};

const actions = {
	/**
	 * Receives plugin data from the server.
	 *
	 * @since 0.4.0
	 *
	 * @param pluginData - Plugin data received from the server, as key value pairs.
	 * @returns Action creator.
	 */
	receivePluginData( pluginData: PluginData ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceivePluginData,
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
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceivePluginData: {
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
	 * @returns Action creator.
	 */
	getPluginData() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const pluginData: PluginData = await apiFetch( {
				path: '/ai-services/v1/self',
			} );
			dispatch.receivePluginData( pluginData );
		};
	},
};

const selectors = {
	getPluginData: ( state: State ) => {
		return state.pluginData;
	},

	getPluginSlug: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_slug;
	} ),

	getPluginBasename: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_basename;
	} ),

	getPluginVersion: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_version;
	} ),

	getPluginHomepageUrl: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_homepage_url;
	} ),

	getPluginSupportUrl: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_support_url;
	} ),

	getPluginContributingUrl: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_contributing_url;
	} ),

	getPluginSettingsUrl: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_settings_url;
	} ),

	getPluginPlaygroundUrl: createRegistrySelector( ( select ) => () => {
		const pluginData: PluginData | undefined =
			select( STORE_NAME ).getPluginData();
		if ( ! pluginData ) {
			return undefined;
		}
		return pluginData.plugin_playground_url;
	} ),

	currentUserCan: createRegistrySelector(
		( select ) =>
			( _state: State, capability: string, ...args: string[] ) => {
				const pluginData: PluginData | undefined =
					select( STORE_NAME ).getPluginData();
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

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
