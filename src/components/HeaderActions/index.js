/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

const { Fill, Slot } = createSlotFill( 'HeaderActions' );

function HeaderActions( { children } ) {
	return <Fill>{ children }</Fill>;
}

HeaderActions.Slot = Slot;

export default HeaderActions;
