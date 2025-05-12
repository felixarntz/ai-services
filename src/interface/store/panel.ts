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
	 * Opens a panel.
	 *
	 * @since 0.4.0
	 *
	 * @param panelId - Panel identifier.
	 * @returns Action creator.
	 */
	openPanel( panelId: string ) {
		return ( { registry }: DispatcherArgs ) => {
			const activePanels =
				registry
					.select( preferencesStore )
					.get( 'wp-starter-plugin', 'activePanels' ) ?? [];
			if ( activePanels.includes( panelId ) ) {
				return;
			}
			registry
				.dispatch( preferencesStore )
				.set( 'wp-starter-plugin', 'activePanels', [
					...activePanels,
					panelId,
				] );
		};
	},

	/**
	 * Closes a panel.
	 *
	 * @since 0.4.0
	 *
	 * @param panelId - Panel identifier.
	 * @returns Action creator.
	 */
	closePanel( panelId: string ) {
		return ( { registry }: DispatcherArgs ) => {
			const activePanels =
				registry
					.select( preferencesStore )
					.get( 'wp-starter-plugin', 'activePanels' ) ?? [];
			if (
				! Array.isArray( activePanels ) ||
				! activePanels.includes( panelId )
			) {
				return;
			}
			registry.dispatch( preferencesStore ).set(
				'wp-starter-plugin',
				'activePanels',
				activePanels.filter(
					( activePanelId ) => activePanelId !== panelId
				)
			);
		};
	},

	/**
	 * Toggles a panel.
	 *
	 * If the panel is active, it will be closed.
	 * If the panel is closed, it will be opened.
	 *
	 * @since 0.4.0
	 *
	 * @param panelId - Panel identifier.
	 * @returns Action creator.
	 */
	togglePanel( panelId: string ) {
		return ( { dispatch, select }: DispatcherArgs ) => {
			if ( select.isPanelActive( panelId ) ) {
				dispatch.closePanel( panelId );
			} else {
				dispatch.openPanel( panelId );
			}
		};
	},
};

const selectors = {
	isPanelActive: createRegistrySelector(
		( select ) =>
			(
				_state: State,
				panelId: string,
				initialOpen: boolean = false
			) => {
				const activePanels = select( preferencesStore ).get(
					'wp-starter-plugin',
					'activePanels'
				);
				if ( ! activePanels || ! Array.isArray( activePanels ) ) {
					return !! initialOpen;
				}
				return !! activePanels.includes( panelId );
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
