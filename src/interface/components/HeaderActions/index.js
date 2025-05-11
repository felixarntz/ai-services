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
} from '@wordpress/components';

const { Fill, Slot } = createSlotFill( 'HeaderActions' );

/**
 * Renders a wrapper for the actions within the header of the application.
 *
 * Any children passed to this component will be rendered inside the header actions area.
 *
 * @since 0.1.0
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Child elements to render.
 * @return {Component} The component to be rendered.
 */
function HeaderActions( { children } ) {
	return <Fill>{ children }</Fill>;
}

HeaderActions.propTypes = {
	children: PropTypes.node.isRequired,
};

HeaderActions.Slot = Slot;

/**
 * Hook to check whether any fills are provided for the HeaderActions slot.
 *
 * @since 0.1.0
 *
 * @return {boolean} True if there are any HeaderActions fills, false otherwise.
 */
export function useHasHeaderActions() {
	const fills = useSlotFills( 'HeaderActions' );
	return !! fills?.length;
}

export default HeaderActions;
