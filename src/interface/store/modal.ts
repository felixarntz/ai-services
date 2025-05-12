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
}

type UnknownAction = Action< ActionType.Unknown >;

export type CombinedAction = UnknownAction;

export type State = {};

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
	 * Opens a modal.
	 *
	 * @since 0.4.0
	 *
	 * @param modalId - Modal identifier.
	 * @returns Action creator.
	 */
	openModal( modalId: string ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( interfaceStore )
				.openModal( `wp-starter-plugin/${ modalId }` );
		};
	},

	/**
	 * Closes the currently open modal.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	closeModal() {
		return ( { registry }: DispatcherArgs ) => {
			registry.dispatch( interfaceStore ).closeModal();
		};
	},

	/**
	 * Toggles a modal.
	 *
	 * If the modal is active, it will be closed.
	 * If the modal is closed or another modal is active, it will be opened.
	 *
	 * @since 0.4.0
	 *
	 * @param modalId - Modal identifier.
	 * @returns Action creator.
	 */
	toggleModal( modalId: string ) {
		return ( { dispatch, select }: DispatcherArgs ) => {
			if ( select.isModalActive( modalId ) ) {
				dispatch.closeModal();
			} else {
				dispatch.openModal( modalId );
			}
		};
	},
};

const selectors = {
	isModalActive: createRegistrySelector(
		( select ) => ( _state: State, modalId: string ) => {
			return select( interfaceStore ).isModalActive(
				`wp-starter-plugin/${ modalId }`
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
	actions,
	reducer: ( state: State ): State => state, // Empty reducer.
	selectors,
};

export default storeConfig;
