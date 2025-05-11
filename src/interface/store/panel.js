/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

const actions = {
	/**
	 * Opens a panel.
	 *
	 * @since 0.4.0
	 *
	 * @param {string} panelId Panel identifier.
	 * @return {Function} Action creator.
	 */
	openPanel( panelId ) {
		return ( { registry } ) => {
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
	 * @param {string} panelId Panel identifier.
	 * @return {Function} Action creator.
	 */
	closePanel( panelId ) {
		return ( { registry } ) => {
			const activePanels =
				registry
					.select( preferencesStore )
					.get( 'wp-starter-plugin', 'activePanels' ) ?? [];
			if ( ! activePanels.includes( panelId ) ) {
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
	 * @param {string} panelId Panel identifier.
	 * @return {Function} Action creator.
	 */
	togglePanel( panelId ) {
		return ( { dispatch, select } ) => {
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
			( state, panelId, initialOpen = false ) => {
				const activePanels = select( preferencesStore ).get(
					'wp-starter-plugin',
					'activePanels'
				);
				if ( ! activePanels ) {
					return !! initialOpen;
				}
				return !! activePanels.includes( panelId );
			}
	),
};

const storeConfig = {
	actions,
	selectors,
};

export default storeConfig;
