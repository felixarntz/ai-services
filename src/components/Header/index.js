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
import HeaderActions from '../HeaderActions';
import './style.scss';

const { Fill, Slot } = createSlotFill( 'Header' );

/**
 * Renders a wrapper for the header of the application.
 *
 * Any children passed to this component will be rendered inside the header.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Header( { children } ) {
	return (
		<Fill>
			<div className="wpoopple-header">
				<div className="wpoopple-header__left">{ children }</div>
				<div className="wpoopple-header__right">
					<HeaderActions.Slot />
				</div>
			</div>
		</Fill>
	);
}

Header.propTypes = {
	children: PropTypes.node.isRequired,
};

Header.Slot = Slot;

export default Header;
