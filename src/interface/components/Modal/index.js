/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	createSlotFill,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUseSlotFills as useSlotFills,
	Modal as CoreModal,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as interfaceStore } from '../../store';

const { Fill, Slot } = createSlotFill( 'Modal' );

/**
 * Renders a modal for the application.
 *
 * Multiple modals can be rendered, but only one can be active at a time.
 *
 * @since 0.4.0
 *
 * @param {Object}  props            Component props.
 * @param {string}  props.identifier Identifier for the modal, to use in the store.
 * @param {string}  props.title      Title of the modal.
 * @param {Element} props.children   Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Modal( { identifier, title, children, ...props } ) {
	const isModalActive = useSelect( ( select ) =>
		select( interfaceStore ).isModalActive( identifier )
	);
	const { closeModal } = useDispatch( interfaceStore );

	if ( ! isModalActive ) {
		return null;
	}

	return (
		<Fill>
			<CoreModal
				title={ title }
				closeButtonLabel={ __( 'Close modal', 'wp-starter-plugin' ) }
				onRequestClose={ closeModal }
				{ ...props }
			>
				{ children }
			</CoreModal>
		</Fill>
	);
}

Modal.propTypes = {
	identifier: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
	children: PropTypes.node.isRequired,
};

Modal.Slot = Slot;

/**
 * Hook to check whether any fills are provided for the Modal slot.
 *
 * @since 0.4.0
 *
 * @return {boolean} True if there are any Modal fills, false otherwise.
 */
export function useHasModal() {
	const fills = useSlotFills( 'Modal' );
	return !! fills?.length;
}

export default Modal;
