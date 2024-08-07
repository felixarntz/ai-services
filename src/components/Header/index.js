/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

/**
 * Internal dependencies
 */
import Interface from '../Interface';
import './style.scss';

const { Fill: HeaderLeftFill, Slot: HeaderLeftSlot } =
	createSlotFill( 'HeaderLeft' );
const { Fill: HeaderRightFill, Slot: HeaderRightSlot } =
	createSlotFill( 'HeaderRight' );

function Header( { children } ) {
	return (
		<Interface.Header>
			<div className="wpoopple-header">
				<div className="wpoopple-header__left">
					<HeaderLeftSlot />
					{ children }
				</div>
				<div className="wpoopple-header__right">
					<HeaderRightSlot />
				</div>
			</div>
		</Interface.Header>
	);
}

Header.Left = HeaderLeftFill;
Header.Right = HeaderRightFill;

export default Header;
