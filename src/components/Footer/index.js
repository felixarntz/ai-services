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

const { Fill, Slot } = createSlotFill( 'Footer' );

/**
 * Renders a wrapper for the footer of the application.
 *
 * Any children passed to this component will be rendered inside the footer.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Footer( { children } ) {
	return <Fill>{ children }</Fill>;
}

Footer.propTypes = {
	children: PropTypes.node.isRequired,
};

Footer.Slot = Slot;

/**
 * Hook to check whether any fills are provided for the Footer slot.
 *
 * @since n.e.x.t
 *
 * @return {boolean} True if there are any Footer fills, false otherwise.
 */
export function useHasFooter() {
	const fills = useSlotFills( 'Footer' );
	return !! fills?.length;
}

export default Footer;
