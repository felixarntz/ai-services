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
