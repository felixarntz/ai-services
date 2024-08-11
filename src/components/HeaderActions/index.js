/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

const { Fill, Slot } = createSlotFill( 'HeaderActions' );

function HeaderActions( { children } ) {
	return <Fill>{ children }</Fill>;
}

HeaderActions.propTypes = {
	children: PropTypes.node.isRequired,
};

HeaderActions.Slot = Slot;

export default HeaderActions;
