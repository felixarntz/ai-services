/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as interfaceStore } from '@wordpress/interface';

/**
 * Internal dependencies
 */
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	SetDefaultSidebar = 'SET_DEFAULT_SIDEBAR',
}

type UnknownAction = Action< ActionType.Unknown >;
type SetDefaultSidebarAction = Action<
	ActionType.SetDefaultSidebar,
	{ sidebarId: string }
>;

export type CombinedAction = UnknownAction | SetDefaultSidebarAction;

export type State = {
	defaultSidebarId: string | false;
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
	defaultSidebarId: false,
};

const actions = {
	/**
	 * Opens a sidebar.
	 *
	 * @since 0.4.0
	 *
	 * @param sidebarId - Sidebar identifier.
	 * @returns Action creator.
	 */
	openSidebar( sidebarId: string ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( interfaceStore )
				.enableComplementaryArea( 'ai-services', sidebarId );
		};
	},

	/**
	 * Closes the currently open sidebar (if any).
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	closeSidebar() {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( interfaceStore )
				.disableComplementaryArea( 'ai-services' );
		};
	},

	/**
	 * Toggles a sidebar.
	 *
	 * If the sidebar is active, it will be closed.
	 * If the sidebar is closed or another sidebar is active, it will be opened.
	 *
	 * @since 0.4.0
	 *
	 * @param sidebarId - Sidebar identifier.
	 * @returns Action creator.
	 */
	toggleSidebar( sidebarId: string ) {
		return ( { dispatch, select }: DispatcherArgs ) => {
			if ( select.isSidebarActive( sidebarId ) ) {
				dispatch.closeSidebar();
			} else {
				dispatch.openSidebar( sidebarId );
			}
		};
	},

	/**
	 * Toggles the default sidebar.
	 *
	 * If a sidebar is active, it will be closed.
	 * If no sidebar is active, the default sidebar will be opened.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	toggleDefaultSidebar() {
		return ( { dispatch, select }: DispatcherArgs ) => {
			if ( select.getActiveSidebar() ) {
				dispatch.closeSidebar();
			} else {
				const defaultSidebarId = select.getDefaultSidebar();
				if ( ! defaultSidebarId ) {
					return;
				}
				dispatch.openSidebar( defaultSidebarId );
			}
		};
	},

	/**
	 * Sets the default sidebar.
	 *
	 * @since 0.4.0
	 *
	 * @param sidebarId - Sidebar identifier.
	 * @returns Action creator.
	 */
	setDefaultSidebar( sidebarId: string ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.SetDefaultSidebar,
				payload: {
					sidebarId,
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
		case ActionType.SetDefaultSidebar: {
			const { sidebarId } = action.payload;
			return {
				...state,
				defaultSidebarId: sidebarId,
			};
		}
	}

	return state;
}

const selectors = {
	getActiveSidebar: createRegistrySelector( ( select ) => () => {
		return select( interfaceStore ).getActiveComplementaryArea(
			'ai-services'
		);
	} ),

	isSidebarActive: createRegistrySelector(
		( select ) => ( _state: State, sidebarId: string ) => {
			return (
				select( interfaceStore ).getActiveComplementaryArea(
					'ai-services'
				) === sidebarId
			);
		}
	),

	getDefaultSidebar: ( state: State ) => {
		return state.defaultSidebarId;
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
	selectors,
};

export default storeConfig;
