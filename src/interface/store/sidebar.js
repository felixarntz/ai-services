/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as interfaceStore } from '@wordpress/interface';

const SET_DEFAULT_SIDEBAR = 'SET_DEFAULT_SIDEBAR';

const initialState = {
	defaultSidebarId: false,
};

const actions = {
	/**
	 * Opens a sidebar.
	 *
	 * @since 0.4.0
	 *
	 * @param {string} sidebarId Sidebar identifier.
	 * @return {Function} Action creator.
	 */
	openSidebar( sidebarId ) {
		return ( { registry } ) => {
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
	 * @return {Function} Action creator.
	 */
	closeSidebar() {
		return ( { registry } ) => {
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
	 * @param {string} sidebarId Sidebar identifier.
	 * @return {Function} Action creator.
	 */
	toggleSidebar( sidebarId ) {
		return ( { dispatch, select } ) => {
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
	 * @return {Function} Action creator.
	 */
	toggleDefaultSidebar() {
		return ( { dispatch, select } ) => {
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
	 * @param {string} sidebarId Sidebar identifier.
	 * @return {Object} Action creator.
	 */
	setDefaultSidebar( sidebarId ) {
		return {
			type: SET_DEFAULT_SIDEBAR,
			payload: { sidebarId },
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
		case SET_DEFAULT_SIDEBAR: {
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
		( select ) => ( state, sidebarId ) => {
			return (
				select( interfaceStore ).getActiveComplementaryArea(
					'ai-services'
				) === sidebarId
			);
		}
	),

	getDefaultSidebar: ( state ) => {
		return state.defaultSidebarId;
	},
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	selectors,
};

export default storeConfig;
