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

/**
 * Internal dependencies
 */
import './style.scss';

const { Fill, Slot } = createSlotFill( 'Header' );

/**
 * Renders a wrapper for the header of the application.
 *
 * Any children passed to this component will be rendered inside the header.
 *
 * @since 0.1.0
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Header( { children } ) {
	return <Fill>{ children }</Fill>;
}

Header.propTypes = {
	children: PropTypes.node.isRequired,
};

Header.Slot = Slot;

/**
 * Hook to check whether any fills are provided for the Header slot.
 *
 * @since 0.1.0
 *
 * @return {boolean} True if there are any Header fills, false otherwise.
 */
export function useHasHeader() {
	const fills = useSlotFills( 'Header' );
	return !! fills?.length;
}

export default Header;
