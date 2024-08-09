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

Header.Slot = Slot;

export default Header;
