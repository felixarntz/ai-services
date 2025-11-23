/**
 * External dependencies
 */
import type { StoreConfig, Action, ThunkArgs } from 'wp-store-utils';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector, createSelector } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import logError from '../utils/log-error';

const PLUGIN_SETTINGS_PREFIX = 'wpsp_';
const SAVE_SETTINGS_NOTICE_ID = 'SAVE_SETTINGS_NOTICE_ID';

type Settings = Record< string, unknown >;

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveSettings = 'RECEIVE_SETTINGS',
	SaveSettingsStart = 'SAVE_SETTINGS_START',
	SaveSettingsFinish = 'SAVE_SETTINGS_FINISH',
	SetSetting = 'SET_SETTING',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveSettingsAction = Action<
	ActionType.ReceiveSettings,
	{ settings: Settings }
>;
type SaveSettingsStartAction = Action< ActionType.SaveSettingsStart >;
type SaveSettingsFinishAction = Action< ActionType.SaveSettingsFinish >;
type SetSettingAction = Action<
	ActionType.SetSetting,
	{ setting: string; value: unknown }
>;

export type CombinedAction =
	| UnknownAction
	| ReceiveSettingsAction
	| SaveSettingsStartAction
	| SaveSettingsFinishAction
	| SetSettingAction;

export type State = {
	savedSettings: Settings | undefined;
	modifiedSettings: Settings;
	optionNameMap: Record< string, string >;
	isSavingSettings: boolean;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

/**
 * Given a string, returns a new string with dash and underscore separators
 * converted to camelCase equivalent.
 *
 * @param input - Input dash- or underscore-delimited string.
 * @returns Camel-cased string.
 */
function camelCase( input: string ): string {
	return input.replace( /-|_([a-z])/g, ( _, letter ) =>
		letter.toUpperCase()
	);
}

/**
 * Updates the modified settings object with the new settings, if they differ from the saved settings.
 *
 * For new settings that are now different from the saved settings, they will be added to the modified settings.
 * For new settings that are now equal to the saved settings, they will be removed from the modified settings.
 *
 * @since n.e.x.t
 *
 * @param modifiedSettings - The modified settings object, as key value pairs.
 * @param savedSettings    - The saved settings object, as key value pairs.
 * @param newSettings      - The new settings object, as key value pairs.
 * @returns The updated modified settings object.
 */
function updateModifiedSettings(
	modifiedSettings: Settings,
	savedSettings: Settings,
	newSettings: Settings
): Settings {
	const updatedSettings = { ...modifiedSettings };

	let hasChanges = false;
	Object.keys( newSettings ).forEach( ( key ) => {
		if ( newSettings[ key ] === modifiedSettings[ key ] ) {
			return;
		}

		hasChanges = true;
		if ( newSettings[ key ] !== savedSettings[ key ] ) {
			updatedSettings[ key ] = newSettings[ key ];
		} else {
			delete updatedSettings[ key ];
		}
	} );

	/*
	 * If there are no changes at all, return the original object to avoid
	 * unnecessary re-renders.
	 */
	if ( ! hasChanges ) {
		return modifiedSettings;
	}

	return updatedSettings;
}

const initialState: State = {
	savedSettings: undefined,
	modifiedSettings: {},
	optionNameMap: {},
	isSavingSettings: false,
};

const actions = {
	/**
	 * Receives settings from the server.
	 *
	 * @since n.e.x.t
	 *
	 * @param settings - Settings received from the server, as key value pairs.
	 * @returns Action creator.
	 */
	receiveSettings( settings: Settings ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveSettings,
				payload: {
					settings,
				},
			} );
		};
	},

	/**
	 * Saves all settings to the server.
	 *
	 * @since n.e.x.t
	 *
	 * @returns Action creator.
	 */
	saveSettings() {
		return async ( { dispatch, select, registry }: DispatcherArgs ) => {
			if ( ! select.areSettingsSaveable() ) {
				return;
			}

			const settings: Settings | undefined = select.getSettings();
			if ( settings === undefined ) {
				return;
			}

			const options: Settings = {};
			Object.keys( settings ).forEach( ( localName ) => {
				const optionName = select.getOptionName( localName );
				if ( ! optionName ) {
					logError(
						`Setting ${ localName } does not correspond to a WordPress option.`
					);
					return;
				}

				if ( ! select.isSettingModified( localName ) ) {
					return;
				}

				options[ optionName ] = settings[ localName ];
			} );

			dispatch( {
				type: ActionType.SaveSettingsStart,
				payload: {},
			} );

			let updatedSettings: Settings = settings;
			try {
				updatedSettings = await apiFetch( {
					path: '/wp/v2/settings',
					method: 'POST',
					data: options,
				} );
			} catch ( error ) {
				logError( error );
			}

			if ( updatedSettings ) {
				dispatch.receiveSettings( updatedSettings );
			}

			dispatch( {
				type: ActionType.SaveSettingsFinish,
				payload: {},
			} );

			if ( updatedSettings ) {
				registry
					.dispatch( noticesStore )
					.createSuccessNotice(
						__(
							'Settings successfully saved.',
							'wp-starter-plugin'
						),
						{
							id: SAVE_SETTINGS_NOTICE_ID,
							type: 'snackbar',
							speak: true,
						}
					);
			} else {
				registry
					.dispatch( noticesStore )
					.createErrorNotice(
						__( 'Saving settings failed.', 'wp-starter-plugin' ),
						{
							id: SAVE_SETTINGS_NOTICE_ID,
							type: 'snackbar',
							speak: true,
						}
					);
			}
		};
	},

	/**
	 * Sets the value for a setting.
	 *
	 * @since n.e.x.t
	 *
	 * @param setting - The setting name.
	 * @param value   - The new value for the setting.
	 * @returns Action creator.
	 */
	setSetting( setting: string, value: unknown ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.SetSetting,
				payload: { setting, value },
			} );
		};
	},

	/**
	 * Sets the value for the deleteData setting.
	 *
	 * @since n.e.x.t
	 *
	 * @param deleteData - The new deleteData value.
	 * @returns Action creator.
	 */
	setDeleteData( deleteData: boolean ) {
		return actions.setSetting( 'deleteData', deleteData );
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since n.e.x.t
 *
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceiveSettings: {
			const { settings } = action.payload;
			const pluginSettings: Settings = {};
			const optionNameMap: Record< string, string > = {};
			Object.keys( settings ).forEach( ( optionName ) => {
				// Skip settings that are not part of the plugin.
				if ( ! optionName.startsWith( PLUGIN_SETTINGS_PREFIX ) ) {
					return;
				}

				const localName = camelCase(
					optionName.replace( PLUGIN_SETTINGS_PREFIX, '' )
				);
				pluginSettings[ localName ] = settings[ optionName ];
				optionNameMap[ localName ] = optionName;
			} );
			return {
				...state,
				savedSettings: pluginSettings,
				modifiedSettings: {},
				optionNameMap,
			};
		}
		case ActionType.SaveSettingsStart: {
			return {
				...state,
				isSavingSettings: true,
			};
		}
		case ActionType.SaveSettingsFinish: {
			return {
				...state,
				isSavingSettings: false,
			};
		}
		case ActionType.SetSetting: {
			const { setting, value } = action.payload;
			if ( state.savedSettings === undefined ) {
				logError(
					`Setting ${ setting } cannot be set before settings are loaded.`
				);
				return state;
			}
			if ( state.savedSettings[ setting ] === undefined ) {
				logError( `Invalid setting ${ setting }.` );
				return state;
			}
			return {
				...state,
				modifiedSettings: updateModifiedSettings(
					state.modifiedSettings,
					state.savedSettings,
					{ [ setting ]: value }
				),
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Fetches the settings from the server.
	 *
	 * @since n.e.x.t
	 *
	 * @returns Action creator.
	 */
	getSettings() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const settings: Settings = await apiFetch( {
				path: '/wp/v2/settings',
			} );
			dispatch.receiveSettings( settings );
		};
	},
};

const selectors = {
	getSettings: createSelector(
		( state: State ) => {
			if ( ! state.savedSettings ) {
				return undefined;
			}
			return {
				...state.savedSettings,
				...state.modifiedSettings,
			};
		},
		( state ) => [ state.savedSettings, state.modifiedSettings ]
	),

	hasModifiedSettings: createSelector(
		( state: State ) => {
			return Object.keys( state.modifiedSettings ).length > 0;
		},
		( state ) => [ state.modifiedSettings ]
	),

	isSavingSettings: ( state: State ) => {
		return state.isSavingSettings;
	},

	areSettingsSaveable: createRegistrySelector( ( select ) => () => {
		if ( select( STORE_NAME ).isSavingSettings() ) {
			return false;
		}

		if ( ! select( STORE_NAME ).hasModifiedSettings() ) {
			return false;
		}

		const settings = select( STORE_NAME ).getSettings();
		return (
			settings !== undefined &&
			! select( STORE_NAME ).isResolving( 'getSettings' )
		);
	} ),

	getSetting: createRegistrySelector(
		( select ) => ( _state: State, setting: string ) => {
			const settings = select( STORE_NAME ).getSettings() as
				| Settings
				| undefined;
			if ( settings === undefined ) {
				return undefined;
			}
			if ( settings[ setting ] === undefined ) {
				logError( `Invalid setting ${ setting }.` );
				return undefined;
			}
			return settings[ setting ] as unknown;
		}
	),

	getDeleteData: ( state: State ) => {
		const setting = selectors.getSetting( state, 'deleteData' );
		if ( setting === undefined ) {
			return undefined;
		}
		return setting as boolean;
	},

	isSettingModified: ( state: State, setting: string ) => {
		return state.modifiedSettings[ setting ] !== undefined;
	},

	getOptionName: ( state: State, setting: string ) => {
		return state.optionNameMap[ setting ];
	},
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
