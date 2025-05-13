/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

/**
 * Internal dependencies
 */
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
}

type UnknownAction = Action< ActionType.Unknown >;

export type CombinedAction = UnknownAction;

export type State = Record< string, never >;

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const actions = {
	/**
	 * Sets a preference.
	 *
	 * @since 0.4.0
	 *
	 * @param name  - Preference name.
	 * @param value - Preference value.
	 * @returns Action creator.
	 */
	setPreference( name: string, value: unknown ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services', name, value );
		};
	},

	/**
	 * Toggles a preference.
	 *
	 * @since 0.4.0
	 *
	 * @param name - Preference name.
	 * @returns Action creator.
	 */
	togglePreference( name: string ) {
		return ( { registry, select }: DispatcherArgs ) => {
			const currentValue = select.getPreference( name );
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services', name, ! currentValue );
		};
	},
};

const selectors = {
	getPreference: createRegistrySelector(
		( select ) => ( _state: State, name: string ) => {
			return select( preferencesStore ).get( 'ai-services', name );
		}
	),
};

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	actions,
	reducer: ( state: State ): State => state, // Empty reducer.
	selectors,
};

export default storeConfig;
