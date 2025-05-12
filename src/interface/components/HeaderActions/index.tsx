/**
 * WordPress dependencies
 */
import {
	createSlotFill,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUseSlotFills as useSlotFills,
} from '@wordpress/components';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { HeaderActionsProps } from './types';

const { Fill, Slot } = createSlotFill( 'HeaderActions' );

/**
 * Renders a wrapper for the actions within the header of the application.
 *
 * Any children passed to this component will be rendered inside the header actions area.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function InternalHeaderActions(
	props: WordPressComponentProps< HeaderActionsProps, null >
) {
	const { children } = props;
	return <Fill>{ children }</Fill>;
}

const HeaderActions = Object.assign( InternalHeaderActions, {
	displayName: 'HeaderActions',
	Slot: Object.assign( Slot, { displayName: 'HeaderActions.Slot' } ),
} );

/**
 * Hook to check whether any fills are provided for the HeaderActions slot.
 *
 * @since 0.1.0
 *
 * @returns True if there are any HeaderActions fills, false otherwise.
 */
export function useHasHeaderActions() {
	const fills = useSlotFills( 'HeaderActions' );
	return !! fills?.length;
}

export default HeaderActions;
