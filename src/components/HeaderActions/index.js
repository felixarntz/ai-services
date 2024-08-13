/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

const { Fill, Slot } = createSlotFill( 'HeaderActions' );

/**
 * Renders a wrapper for the actions within the header of the application.
 *
 * Any children passed to this component will be rendered inside the header actions area.
 *
 * @since n.e.x.t
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

export default HeaderActions;
