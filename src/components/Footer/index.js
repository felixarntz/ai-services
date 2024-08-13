/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

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
	return (
		<Fill>
			<div className="wpoopple-footer">{ children }</div>
		</Fill>
	);
}

Footer.propTypes = {
	children: PropTypes.node.isRequired,
};

Footer.Slot = Slot;

export default Footer;
