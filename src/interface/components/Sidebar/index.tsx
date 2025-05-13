/**
 * WordPress dependencies
 */
import { ComplementaryArea } from '@wordpress/interface';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUseSlotFills as useSlotFills,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { SidebarProps } from './types';

/**
 * Renders a sidebar for the application.
 *
 * Multiple sidebars can be rendered, but only one can be active at a time.
 * Additionally, sidebars can be pinned by the user for easy access. The PinnedSidebars component can be used to render
 * icon buttons for the pinned sidebars.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function InternalSidebar(
	props: WordPressComponentProps< SidebarProps, null >
) {
	const {
		identifier,
		title,
		icon,
		header,
		isPinnable,
		isActiveByDefault,
		children,
	} = props;

	const shortcut = useSelect(
		( select ) =>
			select( keyboardShortcutsStore ).getShortcutRepresentation(
				'ai-services/toggle-sidebar',
				'display'
			),
		[]
	);

	return (
		<ComplementaryArea
			scope="ai-services"
			identifier={ identifier }
			title={ title }
			icon={ icon }
			header={ header }
			isPinnable={ isPinnable }
			isActiveByDefault={ isActiveByDefault }
			toggleShortcut={ shortcut ?? undefined }
			closeLabel={ __( 'Close sidebar', 'ai-services' ) }
		>
			{ children }
		</ComplementaryArea>
	);
}

const InternalSidebarSlot = () => {
	return <ComplementaryArea.Slot scope="ai-services" />;
};

const Sidebar = Object.assign( InternalSidebar, {
	displayName: 'Sidebar',
	Slot: Object.assign( InternalSidebarSlot, { displayName: 'Sidebar.Slot' } ),
} );

/**
 * Hook to check whether any fills are provided for the Sidebar slot.
 *
 * @since 0.1.0
 *
 * @returns True if there are any Sidebar fills, false otherwise.
 */
export function useHasSidebar() {
	const fills = useSlotFills( 'ComplementaryArea/ai-services' );
	return !! fills?.length;
}

export default Sidebar;
