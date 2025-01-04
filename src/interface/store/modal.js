/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as interfaceStore } from '@wordpress/interface';

const actions = {
	/**
	 * Opens a modal.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string} modalId Modal identifier.
	 * @return {Function} Action creator.
	 */
	openModal( modalId ) {
		return ( { registry } ) => {
			registry
				.dispatch( interfaceStore )
				.openModal( `ai-services/${ modalId }` );
		};
	},

	/**
	 * Closes the currently open modal.
	 *
	 * @since n.e.x.t
	 *
	 * @return {Function} Action creator.
	 */
	closeModal() {
		return ( { registry } ) => {
			registry.dispatch( interfaceStore ).closeModal();
		};
	},

	/**
	 * Toggles a modal.
	 *
	 * If the modal is active, it will be closed.
	 * If the modal is closed or another modal is active, it will be opened.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string} modalId Modal identifier.
	 * @return {Function} Action creator.
	 */
	toggleModal( modalId ) {
		return ( { dispatch, select } ) => {
			if ( select.isModalActive( modalId ) ) {
				dispatch.closeModal();
			} else {
				dispatch.openModal( modalId );
			}
		};
	},
};

const selectors = {
	isModalActive: createRegistrySelector( ( select ) => ( state, modalId ) => {
		return select( interfaceStore ).isModalActive(
			`ai-services/${ modalId }`
		);
	} ),
};

const storeConfig = {
	actions,
	selectors,
};

export default storeConfig;
