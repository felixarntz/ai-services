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
import type { HeaderProps } from './types';
import './style.scss';

const { Fill, Slot } = createSlotFill( 'Header' );

/**
 * Renders a wrapper for the header of the application.
 *
 * Any children passed to this component will be rendered inside the header.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function InternalHeader( props: WordPressComponentProps< HeaderProps, null > ) {
	const { children } = props;
	return <Fill>{ children }</Fill>;
}

const Header = Object.assign( InternalHeader, {
	displayName: 'Header',
	Slot: Object.assign( Slot, { displayName: 'Header.Slot' } ),
} );

/**
 * Hook to check whether any fills are provided for the Header slot.
 *
 * @since 0.1.0
 *
 * @returns True if there are any Header fills, false otherwise.
 */
export function useHasHeader() {
	const fills = useSlotFills( 'Header' );
	return !! fills?.length;
}

export default Header;
