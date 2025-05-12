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
import type { FooterProps } from './types';
import './style.scss';

const { Fill, Slot } = createSlotFill( 'Footer' );

/**
 * Renders a wrapper for the footer of the application.
 *
 * Any children passed to this component will be rendered inside the footer.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function InternalFooter( props: WordPressComponentProps< FooterProps, null > ) {
	const { children } = props;
	return <Fill>{ children }</Fill>;
}

const Footer = Object.assign( InternalFooter, {
	displayName: 'Footer',
	Slot: Object.assign( Slot, { displayName: 'Footer.Slot' } ),
} );

/**
 * Hook to check whether any fills are provided for the Footer slot.
 *
 * @since 0.1.0
 *
 * @returns True if there are any Footer fills, false otherwise.
 */
export function useHasFooter() {
	const fills = useSlotFills( 'Footer' );
	return !! fills?.length;
}

export default Footer;
